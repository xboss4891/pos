<?php
// Giả định các biến $orderinfo, $customerinfo, $iteminfo, $printitem, $allcancelitem
// đã được lấy từ Model giống như cách Controller cũ truyền sang View.

$response = [
    'token_no'      => $orderinfo->tokenno,
    'customer_name' => isset($customerinfo->customer_name) ? $customerinfo->customer_name : '',
    'table_name'    => $orderinfo->tablename,
    'order_number'  => $orderinfo->order_id,
    'items'         => [],
    'cancelled_items' => [] // Mặc định rỗng
];

// --- LOGIC XỬ LÝ DANH SÁCH MÓN ---

// Xác định nguồn dữ liệu: Nếu $printitem trống thì lấy $iteminfo, ngược lại lấy $printitem
// Logic này giúp code DRY (Don't Repeat Yourself) hơn so với file gốc viết if-else lặp lại
$source_items = empty($printitem) ? $iteminfo : $printitem;

foreach ($source_items as $item) {
    // 1. Chuẩn bị dữ liệu món chính
    $item_data = [
        'qty'          => $item->menuqty,
        'product_name' => $item->ProductName,
        'notes'        => $item->notes,
        'variant_name' => $item->variantName,
        'addons'       => []
    ];

    // 2. Xử lý Add-ons (Món thêm)
    // Logic gốc: Tách chuỗi ID và Qty, sau đó query DB lấy tên
    if (!empty($item->add_on_id)) {
        $addons_ids = explode(",", $item->add_on_id);
        $addons_qtys = explode(",", $item->addonsqty);
        
        foreach ($addons_ids as $key => $addonsid) {
            // LƯU Ý: Việc query trong vòng lặp (N+1 query) ảnh hưởng hiệu năng.
            // Tôi giữ nguyên logic cũ để đảm bảo chạy đúng, nhưng xem phần tư vấn tối ưu bên dưới.
            $adonsinfo = $this->order_model->read('*', 'add_ons', array('add_on_id' => $addonsid));
            
            if ($adonsinfo) {
                $item_data['addons'][] = [
                    'name' => $adonsinfo->add_on_name,
                    'qty'  => isset($addons_qtys[$key]) ? $addons_qtys[$key] : 1
                ];
            }
        }
    }

    // Đẩy món vào danh sách
    $response['items'][] = $item_data;
}

// --- LOGIC XỬ LÝ MÓN HỦY (CANCEL ITEMS) ---
// Dựa theo file gốc: Chỉ hiển thị cancel items khi $printitem rỗng
if (empty($printitem) && !empty($allcancelitem)) {
    foreach ($allcancelitem as $cancelitem) {
        $response['cancelled_items'][] = [
            'qty'          => $cancelitem->quantity,
            'product_name' => $cancelitem->ProductName,
            'variant_name' => $cancelitem->variantName
        ];
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>