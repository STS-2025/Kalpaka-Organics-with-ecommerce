<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$conn = new mysqli("localhost", "root", "", "kcpl");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

date_default_timezone_set('Asia/Kolkata');
$date_today = date('Y-m-d');

// Fetch today's sales
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
$total_sales_query = "SELECT SUM(oi.price * oi.quantity) AS total_sales
                      FROM order_items oi
                      JOIN orders o ON oi.order_id = o.id";
$result_total = $conn->query($total_sales_query);
$total_sales = $result_total->fetch_assoc()['total_sales'] ?? 0;

// Fetch total customers
$total_customers_query = "SELECT COUNT(DISTINCT id) AS total_customers FROM users";
$result_customers = $conn->query($total_customers_query);
$total_customers = $result_customers->fetch_assoc()['total_customers'] ?? 0;

// Fetch total products
$total_products_query = "SELECT COUNT(*) AS total_products FROM products";
$result_products = $conn->query($total_products_query);
$total_products = $result_products->fetch_assoc()['total_products'] ?? 0;

// Fetch today's sales data
$query = "SELECT 
              oi.product_name,
              oi.quantity,
              o.created_at AS order_date,
              o.total_amount,
              a.full_name,
              a.phone,
              a.address_line1,
              a.address_line2,
              a.city,
              a.state,
              a.postal_code
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN addresses a ON o.address_id = a.id
            WHERE DATE(o.created_at) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date_today);
$stmt->execute();
$result_today = $stmt->get_result();
$sales_data = $result_today->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
      .flex.items-center, .back-button, #searchInput { display: none !important; }
      table, th, td { font-size: 10pt; border: 1px solid #000; border-collapse: collapse; }
      th, td { padding: 4px 6px; }
    }
  </style>
</head>
<body class="bg-gray-100 font-sans">

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
  <a href="admin_login.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i class="fas fa-sign-out-alt w-5"></i> Logout</a>
</div>

<!-- Main content -->
<div class="pt-24 px-6 transition-all duration-300" id="mainContent">

  <!-- Dashboard Cards -->
  <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
      <a href="today_sales.php" class="flex flex-col items-center">
        <i class="fas fa-shopping-bag text-2xl mb-2"></i>
        <div class="text-sm text-gray-200">Today's Sales</div>
        <div class="text-lg font-semibold">₹<?php echo number_format($today_sales,2); ?></div>
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

  <!-- Card container -->
 <div class="bg-white shadow-lg rounded-2xl p-6 border border-teal-200 main-container">
  <!-- Header -->
  <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <h1 class="text-2xl font-bold text-teal-700">✨ Today's Sales (<?php echo htmlspecialchars($date_today); ?>)</h1>
    <div class="flex items-center gap-2">
      <div class="relative">
        <input 
          type="text" 
          id="searchInput" 
          placeholder="Search sales..."
          class="pl-8 pr-3 py-2 text-sm border border-teal-300 rounded-lg shadow-sm focus:ring-2 focus:ring-teal-400 focus:outline-none  bg-white"
        >
        <i class="fa fa-search absolute left-2.5 top-2.5 text-gray-400 text-sm"></i>
      </div>
      <button 
        onclick="window.print()" 
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-500 to-teal-700 rounded-lg shadow-md hover:opacity-90 transition">
        <i class="fa-solid fa-print"></i> <span class="hidden sm:inline">Print</span>
      </button>
    </div>
  </div>

  <!-- Table -->
  <div class="overflow-x-auto">
    <table class="min-w-full text-xs sm:text-sm rounded-xl overflow-hidden" id="salesTable">
      <thead class="bg-teal-700 text-white">
        <tr>
          <th class="px-4 py-3 text-left">Product</th>
          <th class="px-4 py-3 text-left">Qty</th>
          <th class="px-4 py-3 text-left">Date</th>
          <th class="px-4 py-3 text-left">Price</th>
          <th class="px-4 py-3 text-left">Customer</th>
          <th class="px-4 py-3 text-left">Phone</th>
          <th class="px-4 py-3 text-left">Address</th>
          <th class="px-4 py-3 text-center">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        <?php if(!empty($sales_data)) {
          foreach($sales_data as $sale) {
            $product_name = htmlspecialchars($sale['product_name'] ?? '');
            $quantity = (int)($sale['quantity'] ?? 0);
            $order_date = $sale['order_date'] ?? '';
            $total_amount = number_format((float)($sale['total_amount'] ?? 0), 2);
            $full_name = htmlspecialchars($sale['full_name'] ?? '');
            $phone = htmlspecialchars($sale['phone'] ?? '');
            $a1 = htmlspecialchars($sale['address_line1'] ?? '');
            $a2 = htmlspecialchars($sale['address_line2'] ?? '');
            $city = htmlspecialchars($sale['city'] ?? '');
            $state = htmlspecialchars($sale['state'] ?? '');
            $pcode = htmlspecialchars($sale['postal_code'] ?? '');
            $address_parts = array_filter([$a1, $a2, $city, $state . ($pcode ? " - $pcode" : '')]);
            $address_html = htmlspecialchars(implode(', ', $address_parts));

            echo "<tr class='hover:bg-teal-50 transition'>
              <td class='px-4 py-3 text-gray-700 font-medium'>{$product_name}</td>
              <td class='px-4 py-3 text-gray-600'>{$quantity}</td>
              <td class='px-4 py-3 text-gray-600'>" . date('M j, Y g:i a', strtotime($order_date)) . "</td>
              <td class='px-4 py-3 font-semibold text-teal-600'>₹{$total_amount}</td>
              <td class='px-4 py-3 text-gray-700'>{$full_name}</td>
              <td class='px-4 py-3 text-gray-600'>{$phone}</td>
              <td class='px-4 py-3 text-gray-600'>{$address_html}</td>
              <td class='px-4 py-3 text-center flex gap-2 justify-center'>
                <button class='px-2 py-1 text-xs bg-teal-100 text-teal-600 rounded-lg hover:bg-teal-500 hover:text-white transition'>
                  <i class='fas fa-edit'></i><span class='hidden sm:inline'> Edit</span>
                </button>
                <button class='px-2 py-1 text-xs bg-red-100 text-red-600 rounded-lg hover:bg-red-500 hover:text-white transition'>
                  <i class='fas fa-trash'></i><span class='hidden sm:inline'> Delete</span>
                </button>
              </td>
            </tr>";
          }
        } else {
          echo '<tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No sales data available for today.</td></tr>';
        } ?>
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  <div class="text-center mt-6">
    <a href="Admin_home.php" class="inline-block px-6 py-2 text-sm font-medium text-white rounded-lg shadow-md bg-gradient-to-r from-teal-500 to-teal-700 hover:opacity-90 transition back-button">
      <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>
</div>

</div>

<script>
const hamburgerBtn = document.getElementById('hamburgerBtn');
const drawer = document.getElementById('drawer');
const mainContent = document.getElementById('mainContent');

hamburgerBtn.addEventListener('click', () => {
  drawer.classList.toggle('-translate-x-full');
  mainContent.classList.toggle('ml-64');
});

// Search filter
document.getElementById('searchInput').addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  document.querySelectorAll('#salesTable tbody tr').forEach(row => {
    const product = row.cells[0].textContent.toLowerCase();
    const username = row.cells[4].textContent.toLowerCase();
    row.style.display = (product.includes(filter) || username.includes(filter)) ? '' : 'none';
  });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
