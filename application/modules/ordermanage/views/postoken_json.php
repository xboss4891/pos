<?php
// Giả định các biến dữ liệu ($orderinfo, $customerinfo, $iteminfo, $tableinfo, $exitsitem) 
// đã được Controller lấy từ Model (giống như cách view cũ nhận dữ liệu).

$response = [
    'token_no'      => isset($orderinfo->tokenno) ? $orderinfo->tokenno : '',
    'customer_name' => isset($customerinfo->customer_name) ? $customerinfo->customer_name : '',
    'table_name'    => isset($tableinfo->tablename) ? $tableinfo->tablename : '',
    'order_number'  => isset($orderinfo->order_id) ? $orderinfo->order_id : '',
    'view'          => 'place_order',
    'items'         => []
];

// --- PHẦN 1: XỬ LÝ ITEMS CHÍNH ($iteminfo) ---
if (!empty($iteminfo)) {
    foreach ($iteminfo as $item) {
        
        // Chuẩn bị dữ liệu Add-ons (Món thêm)
        $addons_list = [];
        if (!empty($item->add_on_id)) {
            $addons_ids = explode(",", $item->add_on_id);
            $addons_qtys = explode(",", $item->addonsqty);
            
            foreach ($addons_ids as $key => $addonsid) {
                // Query lấy tên Addon (Giữ nguyên logic query trong loop của file gốc)
                $adonsinfo = $this->order_model->read('*', 'add_ons', ['add_on_id' => $addonsid]);
                
                if ($adonsinfo) {
                    $addons_list[] = [
                        'name' => $adonsinfo->add_on_name,
                        'qty'  => isset($addons_qtys[$key]) ? $addons_qtys[$key] : 1
                    ];
                }
            }
        }

        // Logic kiểm tra Item Update trong file gốc
        // File gốc có đoạn if/else kiểm tra $newitem->isupdate == 1
        // Tuy nhiên cả 2 nhánh if/else đều in ra HTML giống hệt nhau cho items.
        // Nên ta đưa thẳng vào danh sách mà không cần tách nhánh để JSON gọn gàng.
        
        $response['items'][] = [
            'type'         => 'main', // Đánh dấu là món chính
            'qty'          => $item->menuqty,
            'product_name' => $item->ProductName,
            'notes'        => $item->notes,
            'variant_name' => $item->variantName,
            'addons'       => $addons_list
        ];
    }
}

// --- PHẦN 2: XỬ LÝ ITEMS CẬP NHẬT ($exitsitem) ---
if (isset($exitsitem) && !empty($exitsitem)) {
    foreach ($exitsitem as $exititem) {
        // Query kiểm tra trạng thái update
        $newitem = $this->order_model->read('*', 'order_menu', ['row_id' => $exititem->row_id, 'isupdate' => 1]);
        
        // Query tính tổng số lượng update
        $isexitsitem = $this->order_model->readupdate(
            'tbl_updateitems.*,SUM(tbl_updateitems.qty) as totalqty', 
            'tbl_updateitems', 
            [
                'ordid'     => $orderinfo->order_id, 
                'menuid'    => $exititem->menu_id, 
                'varientid' => $exititem->varientid, 
                'addonsuid' => $exititem->addonsuid
            ]
        );

        if (!empty($isexitsitem) && $isexitsitem->totalqty > 0) {
            // Logic quan trọng trong file gốc:
            // Nếu @$newitem->isupdate == 1 thì echo "" (KHÔNG IN GÌ CẢ)
            // Ngược lại mới in row.
            
            if (@$newitem->isupdate == 1) {
                // Bỏ qua, không làm gì (theo logic gốc)
                continue; 
            } else {
                // Thêm vào danh sách trả về
                $response['items'][] = [
                    'type'         => 'updated', // Đánh dấu là món update
                    'qty'          => $isexitsitem->totalqty, // Lưu ý: Dùng totalqty từ bảng update
                    'product_name' => $exititem->ProductName,
                    'notes'        => $exititem->notes,
                    'variant_name' => $exititem->variantName,
                    'addons'       => [] // File gốc vòng lặp exitsitem không có logic in addons
                ];
            }
        }
    }
}

// --- TRẢ VỀ JSON ---
//header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>