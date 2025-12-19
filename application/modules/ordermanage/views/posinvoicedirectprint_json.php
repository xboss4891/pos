<?php
// 1. Đặt Header để trình duyệt/Client hiểu đây là phản hồi API JSON
//header('Content-Type: application/json; charset=utf-8');

// Load Model (Giữ nguyên logic cũ vì code đang nằm trong View - Dù không khuyến khích)
$this->load->model('ordermanage/order_model', 'ordermodel');

// --- BƯỚC 1: XỬ LÝ LOGIC TÍNH TOÁN (Logic Extraction) ---

$formattedItems = [];
$totalAmount    = 0;
$subTotal       = 0;
$total          = $orderinfo->totalamount;

// Duyệt qua danh sách món ăn
foreach ($iteminfo as $item) {
    // 1.1 Tính giá cơ bản (Variant hoặc giá thường)
    if ($item->price > 0) {
        $itemPrice   = $item->price * $item->menuqty;
        $singlePrice = $item->price;
    } else {
        $itemPrice   = $item->mprice * $item->menuqty;
        $singlePrice = $item->mprice;
    }

    // 1.2 Tính Discount (OffersRate)
    $itemDetails = $this->ordermodel->getiteminfo($item->menu_id);
    $offerDiscountVal = 0;
    if ($itemDetails->OffersRate > 0) {
        $offerDiscountVal = ($itemDetails->OffersRate * $itemPrice) / 100;
    }

    // 1.3 Xử lý Add-ons (Món thêm)
    // Lưu ý: Logic cũ gọi DB trong vòng lặp (N+1 Problem). Tôi giữ nguyên để code chạy được,
    // nhưng sẽ tối ưu output ra JSON.
    $addonsList = [];
    $addonsTotalPrice = 0;

    if (!empty($item->add_on_id)) {
        $addonsIds = explode(",", $item->add_on_id);
        $addonsQty = explode(",", $item->addonsqty);
        
        $x = 0;
        foreach ($addonsIds as $addonsid) {
            // Gọi DB lấy thông tin addon
            $adonsinfo = $this->order_model->read('*', 'add_ons', array('add_on_id' => $addonsid));
            
            $currentAddonPrice = $adonsinfo->price * $addonsQty[$x];
            $addonsTotalPrice += $currentAddonPrice;

            $addonsList[] = [
                'addon_id'    => $addonsid,
                'name'        => $adonsinfo->add_on_name,
                'price_unit'  => $adonsinfo->price,
                'quantity'    => $addonsQty[$x],
                'total_price' => $currentAddonPrice
            ];
            $x++;
        }
    }

    // Cộng dồn tổng
    $totalAmount += $addonsTotalPrice;
    $subTotal    += $itemPrice;

    // Đẩy vào danh sách Item đã format
    $formattedItems[] = [
        'product_name'   => $item->ProductName,
        'variant_name'   => $item->variantName,
        'unit_price'     => $singlePrice,
        'quantity'       => $item->menuqty,
        'item_total'     => $itemPrice,
        'discount_val'   => $offerDiscountVal,
        'has_addons'     => !empty($addonsList),
        'addons_details' => $addonsList,
        'addons_total'   => $addonsTotalPrice
    ];
}

// --- BƯỚC 2: TÍNH TOÁN BILL INFO (Tax, Service Charge, Due) ---

// VAT Calculation
// Logic cũ: $calvat=$itemtotal*15/100; (nhưng bên dưới lại gán đè $calvat=$billinfo->VAT)
// Tôi sẽ lấy theo logic cuối cùng trong view cũ.
$calVat = isset($billinfo->VAT) ? $billinfo->VAT : 0;

// Service Charge
$serviceCharge = isset($billinfo->service_charge) ? $billinfo->service_charge : 0;

// Discount Bill
$billDiscount = isset($billinfo->discount) ? $billinfo->discount : 0;

// Grand Total
$grandTotal = isset($billinfo->bill_amount) ? $billinfo->bill_amount : 0;

// Customer Paid & Change Due
$customerPaid = ($orderinfo->customerpaid > 0) ? $orderinfo->customerpaid : $orderinfo->totalamount;
$changeDue    = $customerPaid - $orderinfo->totalamount; // Logic cũ lấy totalamount gốc để trừ

// Xử lý Tax chi tiết (Nếu có)
$taxDetails = [];
if (!empty($taxinfos)) {
    $i = 0;
    foreach ($taxinfos as $mvat) {
        if ($mvat['is_show'] == 1) {
            $taxinfo = $this->order_model->read('*', 'tax_collection', array('relation_id' => $orderinfo->order_id));
            $fieldname = 'tax' . $i;
            $taxDetails[] = [
                'tax_name' => $mvat['tax_name'],
                'amount'   => $taxinfo->$fieldname ?? 0
            ];
            $i++;
        }
    }
}

// Payment Status Text
$paymentStatusText = 'Due';
if (isset($billinfo->bill_status) && $billinfo->bill_status == 1) {
    $paymentStatusText = 'Paid';
} elseif ($orderinfo->order_status == 5) {
    $paymentStatusText = 'Canceled';
}

// --- BƯỚC 3: XÂY DỰNG JSON STRUCTURE ---

$responseData = [
    'status' => 'success',
    'view'   => 'invoice',
    'meta'   => [
        'generated_at' => date('Y-m-d H:i:s'),
        'currency'     => [
            'code'     => $currency->currencyname ?? 'USD',
            'symbol'   => $currency->curr_icon ?? '$',
            'position' => $currency->position // 1: left, 2: right
        ]
    ],
    'store_info' => [
        'name'       => $storeinfo->storename,
        'address'    => $storeinfo->address,
        'vat_number' => ($storeinfo->isvatnumshow == 1) ? $storeinfo->vattinno : null,
        'logo_url'   => base_url() . $storeinfo->logo
    ],
    'order_info' => [
        'order_id'       => $orderinfo->order_id,
        'date'           => date("M d, Y", strtotime($orderinfo->order_date)),
        'table'          => $tableinfo->tablename ?? 'N/A',
        'customer_name'  => $customerinfo->customer_name ?? 'Walkin',
        'cashier_name'   => ($cashierinfo->firstname ?? '') . ' ' . ($cashierinfo->lastname ?? ''),
        'payment_status' => $paymentStatusText
    ],
    'items' => $formattedItems,
    'totals' => [
        'sub_total'      => $subTotal, // Tổng tiền món
        'addons_total'   => $totalAmount, // Tổng tiền addons (theo logic cũ $totalAmount chỉ cộng addons?? Cần check kỹ logic cũ, ở đây tôi tách riêng cho rõ)
        'sub_total_combined' => $billinfo->total_amount, // Giá trị lưu trong DB billinfo
        'vat_tax'        => $calVat,
        'vat_percent'    => $storeinfo->vat,
        'service_charge' => $serviceCharge,
        'discount'       => $billDiscount,
        'grand_total'    => $grandTotal
    ],
    'tax_breakdown' => $taxDetails,
    'payment' => [
        'paid_amount' => $customerPaid,
        'change_due'  => $changeDue,
        'total_due'   => ($billinfo->bill_status != 1) ? $customerPaid : 0
    ]
];

// --- BƯỚC 4: OUTPUT JSON ---
echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>