<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once "../php/db.php";


date_default_timezone_set('Asia/Kolkata');
$date_today = date('Y-m-d');

// --- Dashboard Data Fetching ---
// Fetch today's sales
$date_today = date('Y-m-d');
$today_sales_query = "SELECT SUM(oi.price * oi.quantity) AS total_today_sales
                      FROM order_items oi
                      JOIN orders o ON oi.order_id = o.id
                      WHERE DATE(o.created_at) = ?";
$stmt = $conn->prepare($today_sales_query);
$stmt->bind_param("s", $date_today);
$stmt->execute();
$result_today = $stmt->get_result();
$today_sales = $result_today->fetch_assoc()['total_today_sales'] ?? 0;
$stmt->close();

// Fetch total sales
// ✅ Fetch total sales correctly from orders table
$total_sales_query = "SELECT SUM(total_amount) AS total_sales FROM orders";
$result_total = $conn->query($total_sales_query);
$total_sales = $result_total->fetch_assoc()['total_sales'] ?? 0;

$total_customers_query = "SELECT COUNT(DISTINCT id) AS total_customers FROM users";
$result_customers = $conn->query($total_customers_query);
$total_customers = $result_customers->fetch_assoc()['total_customers'] ?? 0;

$total_products_query = "SELECT COUNT(*) AS total_products FROM products";
$result_products = $conn->query($total_products_query);
$total_products = $result_products->fetch_assoc()['total_products'] ?? 0;

// --- Fetch today's detailed sales data for the table ---
$query = "SELECT 
              oi.order_id, oi.product_name, oi.quantity,
              o.created_at AS order_date, o.total_amount,
              a.full_name, a.phone,
              a.address_line1, a.address_line2, a.city, a.state, a.postal_code
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN addresses a ON o.address_id = a.id
            WHERE DATE(o.created_at) = ?
            ORDER BY o.created_at DESC, oi.product_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date_today);
$stmt->execute();
$result_data = $stmt->get_result();
$sales_data = $result_data->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Grouping Logic ---
$grouped_sales_data = [];
$grand_total = 0;
foreach ($sales_data as $sale) {
    $id = $sale['order_id'];
    if (!isset($grouped_sales_data[$id])) {
        $grouped_sales_data[$id] = [
            'order_details'=>[
                'order_id'=>$sale['order_id'],
                'order_date'=>$sale['order_date'],
                'total_amount'=>$sale['total_amount'],
                'full_name'=>$sale['full_name'],
                'phone'=>$sale['phone'],
                'address'=> implode(', ', array_filter([
                    $sale['address_line1'], 
                    $sale['address_line2'], 
                    $sale['city'], 
                    $sale['state'] . (empty($sale['postal_code']) ? '' : ' - ' . $sale['postal_code'])
                ]))
            ],
            'products'=>[]
        ];
        $grand_total += (float)$sale['total_amount'];
    }
    $grouped_sales_data[$id]['products'][] = [
        'product_name'=>$sale['product_name'],
        'quantity'=>$sale['quantity']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Today's Sales</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; }
@media print {
  body * { visibility: hidden; }
  .main-container, .main-container * { visibility: visible; }
  .main-container { position: static; margin: 0; padding: 0; width: 100%; box-shadow: none; }
  #searchInput, .back-button, .flex.items-center button { display: none !important; }
  table, th, td { font-size: 10pt; border: 1px solid #000; border-collapse: collapse; }
  th, td { padding: 4px 6px; }
  h1 { font-size: 16pt; text-align: center; }
}
</style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

<!-- Navbar -->
<div class="fixed top-0 left-0 w-full h-16 bg-teal-700 text-white flex items-center justify-between px-6 shadow-md z-50">
  <button id="hamburgerBtn" class="text-white text-2xl focus:outline-none"><i class="fas fa-bars"></i></button>
  <div class="text-xl font-semibold">Kalpaka Organics</div>
  <div class="flex items-center gap-2 font-semibold text-yellow-400">Welcome Admin 🙏</div>
</div>

<!-- Drawer -->
<div id="drawer" class="fixed top-16 left-0 w-64 h-full bg-teal-700 text-white transform -translate-x-full transition-transform duration-300 z-40 flex flex-col">
  <div class="p-4 font-bold text-lg bg-teal-800 border-b border-teal-600">Menu</div>
  <a href="Admin_home.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-book w-5"></i> Records</a>
  <a href="ui5manageusers.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-users w-5"></i> Users</a>
  <a href="ui5manageproduct.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-box w-5"></i> Products</a>
  <a href="addnewproduct.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-plus w-5"></i> Add Product</a>
  <a href="payment_check.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-university w-5"></i> Payment</a>
  <a href="delivery.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-truck w-5"></i> Delivery</a>
  <a href="../html/shop.html" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-sign-out-alt w-5"></i> Logout</a>
</div>

<!-- Main content -->
<div class="pt-24 px-6 transition-all duration-300" id="mainContent">
  <!-- Dashboard Cards -->
  <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
      <a href="today_sales.php" class="flex flex-col items-center">
        <i class="fas fa-shopping-bag text-2xl mb-2"></i>
        <div class="text-sm text-gray-200">Today's Sales</div>
        <div class="text-lg font-semibold">₹<?=number_format($grand_total,2);?></div>
      </a>
    </div>
    <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
      <a href="total_sales.php" class="flex flex-col items-center">
        <i class="fas fa-chart-line text-2xl mb-2"></i>
        <div class="text-sm text-gray-200">Total Sales</div>
        <div class="text-lg font-semibold">₹<?php echo number_format($total_sales,2); ?></div>
      </a>
    </div>
    <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
      <a href="total_customers.php" class="flex flex-col items-center">
        <i class="fas fa-users text-2xl mb-2"></i>
        <div class="text-sm text-gray-200">Total Customers</div>
        <div class="text-lg font-semibold"><?php echo $total_customers; ?></div>
      </a>
    </div>
    <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
      <a href="total_products.php" class="flex flex-col items-center">
        <i class="fas fa-box text-2xl mb-2"></i>
        <div class="text-sm text-gray-200">Total Products</div>
        <div class="text-lg font-semibold"><?php echo $total_products; ?></div>
      </a>
    </div>
  </div>

  <!-- Sales Table -->
  <div class="main-container max-w-7xl mx-auto p-6">
    <div class="bg-white shadow-lg rounded-2xl p-6 border border-teal-200">
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-teal-700">✨ Today's Sales (<?php echo htmlspecialchars($date_today); ?>)</h1>
        <div class="flex items-center gap-2">
          <div class="relative">
            <input type="text" id="searchInput" placeholder="Search sales..."
              class="pl-8 pr-3 py-2 text-sm border border-teal-300 rounded-lg shadow-sm focus:ring-2 focus:ring-teal-400 focus:outline-none bg-white">
            <i class="fa fa-search absolute left-2.5 top-2.5 text-gray-400 text-sm"></i>
          </div>
          <button onclick="window.print()"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-500 to-teal-700 rounded-lg shadow-md hover:opacity-90 transition">
            <i class="fa-solid fa-print"></i> <span class="hidden sm:inline">Print</span>
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table id="salesTable" class="min-w-full text-xs sm:text-sm rounded-xl overflow-hidden">
          <thead class="bg-teal-700 text-white">
            <tr>
              <th class="px-4 py-3 text-center">Order ID</th>
              <th class="px-4 py-3 text-center">Product</th>
              <th class="px-4 py-3 text-center">Quantity</th>
              <th class="px-4 py-3 text-center">Date Ordered</th>
              <th class="px-4 py-3 text-center">Total Price</th>
              <th class="px-4 py-3 text-center">Customer</th>
              <th class="px-4 py-3 text-center">Phone</th>
              <th class="px-4 py-3 text-center">Address</th>
            </tr>
          </thead>

          <tbody id="salesTableBody" class="divide-y divide-gray-100 bg-white text-gray-700">
          <?php if (!empty($grouped_sales_data)): ?>
            <?php foreach ($grouped_sales_data as $order): 
              $productCount = count($order['products']); 
              $first = true; ?>
              <?php foreach($order['products'] as $product): ?>
                <tr class="hover:bg-teal-50 transition" data-order-id="<?=htmlspecialchars($order['order_details']['order_id']);?>">
                  <?php if ($first): ?>
                    <td rowspan="<?=$productCount?>" class="px-4 py-3 font-medium text-gray-900 data-search-order-id"><?=htmlspecialchars($order['order_details']['order_id']);?></td>
                  <?php endif; ?>
                  <td class="px-4 py-3 data-search-product"><?=htmlspecialchars($product['product_name']);?></td>
                  <td class="px-4 py-3 text-center"><?=htmlspecialchars($product['quantity']);?></td>
                  <?php if ($first): ?>
                    <td rowspan="<?=$productCount?>" class="px-4 py-3 text-gray-600"><?=date('M j, Y g:i a',strtotime($order['order_details']['order_date']));?></td>
                    <td rowspan="<?=$productCount?>" class="px-4 py-3 font-semibold text-teal-600">₹<?=number_format($order['order_details']['total_amount'],2);?></td>
                    <td rowspan="<?=$productCount?>" class="px-4 py-3 data-search-customer"><?=htmlspecialchars($order['order_details']['full_name']);?></td>
                    <td rowspan="<?=$productCount?>" class="px-4 py-3 data-search-phone text-gray-600"><?=htmlspecialchars($order['order_details']['phone']);?></td>
                    <td rowspan="<?=$productCount?>" class="px-4 py-3 text-gray-600"><?=htmlspecialchars($order['order_details']['address']);?></td>
                  <?php endif; $first=false; ?>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="px-4 py-6 text-center text-gray-500 italic">No sales data found for today (<?php echo htmlspecialchars($date_today); ?>).</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
        <a href="Admin_home.php" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-500 to-teal-700 rounded-lg shadow-md hover:opacity-90 transition back-button">
          <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <div class="text-lg font-bold text-teal-700">Grand Total Today: ₹<?=number_format($grand_total,2);?></div>
      </div>
    </div>
  </div>
</div>

<script>
// Drawer toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const drawer = document.getElementById('drawer');
const mainContent = document.getElementById('mainContent');
const searchInput = document.getElementById('searchInput');
const salesTableBody = document.getElementById('salesTableBody');

hamburgerBtn.addEventListener('click', () => {
  drawer.classList.toggle('-translate-x-full');
  if (window.innerWidth >= 1024) mainContent.classList.toggle('lg:ml-64');
});

// --- Live Search via search_orders.php ---
searchInput.addEventListener('keyup', async function() {
  const query = this.value.trim();
  if (query === '') { location.reload(); return; }

  try {
    const res = await fetch(`search_today_orders.php?search=${encodeURIComponent(query)}`);
    const html = await res.text();
    if (html.startsWith('<br') || html.startsWith('<!DOCTYPE')) return;
    salesTableBody.innerHTML = html;
  } catch (err) {
    console.error('Search fetch failed:', err);
  }
});
</script>
</body>
</html>
<?php $conn->close(); ?>
