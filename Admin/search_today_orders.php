<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../php/db.php";


date_default_timezone_set('Asia/Kolkata');
$date_today = date('Y-m-d');  // Today's date

$search = $_GET['search'] ?? '';

// --- Fetch filtered orders for today ---
$query = "SELECT 
            oi.order_id, oi.product_name, oi.quantity,oi.price,
            o.created_at AS order_date, o.total_amount,
            a.full_name, a.phone,
            a.address_line1, a.address_line2, a.city, a.state, a.postal_code
          FROM order_items oi
          JOIN orders o ON oi.order_id = o.id
          JOIN addresses a ON o.address_id = a.id
          WHERE DATE(o.created_at) = ?
            AND (
              oi.product_name LIKE ? OR 
              o.id LIKE ? OR
              a.full_name LIKE ?
            )
          ORDER BY o.created_at DESC, oi.product_name ASC";

$stmt = $conn->prepare($query);
$like = "%$search%";
$stmt->bind_param("ssss", $date_today, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$sales_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Grouping logic same as main page ---
$grouped_sales_data = [];
foreach($sales_data as $sale){
    $id = $sale['order_id'];
    if(!isset($grouped_sales_data[$id])){
        $grouped_sales_data[$id] = [
            'order_details'=>[
                'order_id'=>$sale['order_id'],
                'order_date'=>$sale['order_date'],
                'total_amount'=>$sale['total_amount'],
                'full_name'=>$sale['full_name'],
                'phone'=>$sale['phone'],
                'address'=>implode(', ', array_filter([$sale['address_line1'],$sale['address_line2'],$sale['city'],$sale['state'] . (empty($sale['postal_code'])?'':' - '.$sale['postal_code'])]))
            ],
            'products'=>[]
        ];
    }
    $grouped_sales_data[$id]['products'][] = [
    'product_name'=>$sale['product_name'],
    'quantity'=>$sale['quantity'],
    'price'=>$sale['price']
];

}

// --- Output rows for table ---
foreach($grouped_sales_data as $order){
    $productCount = count($order['products']);
    $first = true;
    foreach($order['products'] as $product){
        echo '<tr data-order-id="'.$order['order_details']['order_id'].'" class="hover:bg-teal-50 transition">';
        if($first){
            echo '<td rowspan="'.$productCount.'" class="px-4 py-3 font-medium text-gray-900 data-search-order-id">'.$order['order_details']['order_id'].'</td>';
        }
        echo '<td class="px-4 py-3 data-search-product">'.$product['product_name'].'</td>';
        echo '<td class="px-4 py-3 text-center">'.$product['quantity'].'</td>';
        if($first){
            echo '<td rowspan="'.$productCount.'" class="px-4 py-3 text-gray-600">'.date('M j, Y g:i a',strtotime($order['order_details']['order_date'])).'</td>';
            $totalPrice = !empty($search) ? ($product['price'] * $product['quantity']) : $order['order_details']['total_amount'];
echo '<td rowspan="'.$productCount.'" class="px-4 py-3 font-semibold text-teal-600">₹'.number_format($totalPrice,2).'</td>';

            echo '<td rowspan="'.$productCount.'" class="px-4 py-3 data-search-customer">'.$order['order_details']['full_name'].'</td>';
            echo '<td rowspan="'.$productCount.'" class="px-4 py-3 data-search-phone text-gray-600">'.$order['order_details']['phone'].'</td>';
            echo '<td rowspan="'.$productCount.'" class="px-4 py-3 text-gray-600">'.$order['order_details']['address'].'</td>';
        }
        $first=false;
        echo '</tr>';
    }
}

$conn->close();
?>
