<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
  session_start();
  if (!isset($_SESSION['username'])) {
    // header("Location: login.php");
    exit;
  }
}

// Database connection
$conn = new mysqli("localhost", "root", "", "kcpl");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// LOW STOCK FETCH 
$lowStockSql = "SELECT p.name AS product_name, v.weight, v.quantity
                FROM product_variants v
                JOIN products p ON v.product_id = p.id
                WHERE v.quantity < 10";
$lowStockResult = $conn->query($lowStockSql);

$lowStockItems = [];
if ($lowStockResult && $lowStockResult->num_rows > 0) {
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStockItems[] = $row;
    }
}


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

// Fetch top 10 products by quantity ordered
$top_products_query = "SELECT oi.product_name, SUM(oi.quantity) AS total_ordered
                       FROM order_items oi
                       GROUP BY oi.product_name
                       ORDER BY total_ordered DESC
                       LIMIT 10";
$result_top_products = $conn->query($top_products_query);

// Fetch top 10 royal customers
$top_customers_query = "
    SELECT u.id, u.name, COUNT(o.id) AS order_count
    FROM orders o
    JOIN users u ON o.user_id=u.id
    GROUP BY u.id, u.name
    ORDER BY order_count DESC
    LIMIT 10
";
$result_top_customers = $conn->query($top_customers_query);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --primary-blue: #00509e;
      --darker-blue: #002244;
      --white: #ffffff;
      --light-gray: #f8f9fa;
      --accent-blue: #4169E1;
    }

    body {
      font-family: 'Poppins', sans-serif;
      color: #333;
      background-color: var(--light-gray);
      transition: margin-left 0.3s ease;
    }

    /* Navbar */
    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 60px;
      background-color: var(--primary-blue);
      color: #fff;
      padding: 0 20px;
      z-index: 2000;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
    }

    .hamburger {
      font-size: 22px;
      background: none;
      border: none;
      color: #fff;
      cursor: pointer;
    }

    .hamburger:hover {
      transform: scale(1.1);
    }

    .brand {
      font-weight: 600;
      font-size: 20px;
      flex: 1;
      text-align: center;
      color: #fff;
    }

    .welcome {
      font-weight: 600;
      color: #FFD700;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* Drawer */
    .drawer {
      position: fixed;
      top: 60px;
      left: -250px;
      width: 250px;
      height: 100%;
      background: var(--primary-blue);
      color: #fff;
      box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15);
      border-top-right-radius: 12px;
      border-bottom-right-radius: 12px;
      transition: left 0.3s ease;
      z-index: 1500;
      display: flex;
      flex-direction: column;
    }

    .drawer.open {
      left: 0;
    }

    .drawer .title {
      padding: 16px;
      font-size: 22px;
      font-weight: 700;
      text-align: left;
      background: #004080;
      color: #fff;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      border-top-right-radius: 12px;
    }

    .drawer a {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      color: #fff;
      text-decoration: none;
      font-size: 18px;
      font-weight: 600;
      border-radius: 6px;
      margin: 4px 8px;
      transition: all 0.2s ease;
    }

    .drawer a i {
      font-size: 20px;
      width: 28px;
      color: #fff;
    }

    .drawer a:hover {
      background: #cce0ff;
      color: #003366;
      border-left: 4px solid var(--primary-blue);
      padding-left: 14px;
    }

    /* Main content */
    .main-content {
      padding-top: 90px;
      padding-left: 20px;
      padding-right: 20px;
      transition: margin-left 0.3s ease, width 0.3s ease;
    }

    body.drawer-open .main-content {
      margin-left: 250px;
      width: calc(100% - 250px);
    }

    /* Cards */
    .card-container {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      max-width: 1000px;
      margin: auto;
      transition: width 0.3s ease;
    }

    body.drawer-open .card-container {
      width: calc(100% - 250px);
    }

    .card {
      background-color: var(--primary-blue);
      color: var(--white);
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
      padding: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .card a {
      color: var(--white);
      text-decoration: none;
      display: block;
    }

    .card i {
      font-size: 1.6rem;
      margin-bottom: 5px;
    }

    .card-title {
      font-size: 0.8rem;
      font-weight: 500;
      margin-bottom: 2px;
      color: rgba(255, 255, 255, 0.8);
    }

    .card-value {
      font-size: 1rem;
      font-weight: 600;
    }

    /* Table styles */
    .table-container {
      background-color: var(--white);
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      margin-bottom: 40px;
    }

    .table {
      border-radius: 12px;
      overflow: hidden;
    }

    .table th {
      background-color: var(--primary-blue);
      color: var(--white);
      text-align: center;
      font-weight: 600;
      font-size: 14px;
    }

    .table td {
      text-align: center;
      vertical-align: middle;
      padding: 12px 8px;
      font-size: 14px;
    }

    .table-striped>tbody>tr:nth-of-type(odd) {
      background-color: #f6f8fa;
    }

    .table-striped>tbody>tr:nth-of-type(even) {
      background-color: #ffffff;
    }

    .table-hover>tbody>tr:hover {
      background-color: #e0ebff;
      transform: translateX(2px);
      transition: 0.2s;
    }

    /* Buttons */
    .view-button {
      background-color: var(--accent-blue);
      border: none;
      color: #fff;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 13px;
      min-width: 80px;
      transition: 0.2s;
    }
    /* Low stock popup */
#lowStockPopup { position: fixed; top: 20px; right: 20px; background-color: #dc3545; color: #fff; padding: 15px; border-radius: 10px; z-index: 9999; max-width: 300px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
#lowStockPopup ul { padding-left: 15px; margin-top: 5px; }

    .view-button:hover {
      background-color: #3157b1;
      transform: translateY(-2px);
    }

    /* Search input */
    .form-control {
      border-radius: 8px;
      border: 1px solid #ddd;
      padding: 8px;
      font-size: 14px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .card-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .main-content {
        padding-top: 80px;
      }

      .card {
        padding: 10px;
      }

      .card i {
        font-size: 1.5rem;
      }

      .card-title {
        font-size: 0.7rem;
      }

      .card-value {
        font-size: 1rem;
      }

      .view-button {
        padding: 5px 10px;
        font-size: 11px;
        min-width: unset;
      }

      body.drawer-open .main-content,
      body.drawer-open .card-container,
      body.drawer-open .table-container {
        width: 100%;
        margin-left: 0;
      }
    }
  </style>
</head>

<body class="bg-gray-100 font-sans">

  <!-- Navbar -->
  <div
    class="fixed top-0 left-0 w-full h-16 bg-teal-700 text-white flex items-center justify-between px-6 shadow-md z-50">
    <button id="hamburgerBtn" class="text-white text-2xl focus:outline-none"><i class="fas fa-bars"></i></button>
    <div class="text-xl font-semibold">Kalpaka Organics</div>
    <div class="flex items-center gap-2 font-semibold text-yellow-400">Welcome Admin 🙏</div>
  </div>

  <!-- Drawer -->
  <div id="drawer"
    class="fixed top-16 left-0 w-64 h-full bg-teal-700 text-white transform -translate-x-full transition-transform duration-300 z-40 flex flex-col">
    <div class="p-4 font-bold text-lg bg-teal-800 border-b border-teal-600">Menu</div>
    <a href="Admin_home.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-book w-5"></i> Records</a>
    <a href="ui5manageusers.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-users w-5"></i> Users</a>
    <a href="ui5manageproduct.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-box w-5"></i> Products</a>
    <a href="addnewproduct.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-plus w-5"></i> Add Product</a>
    <a href="payment_check.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-university w-5"></i> Payment</a>
    <a href="delivery.php" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-truck w-5"></i> Delivery</a>
    <a href="/ko_test_mith/html/shop.html" class="flex items-center gap-3 px-4 py-3 hover:bg-[#2bcdaa] transition"><i
        class="fas fa-sign-out-alt w-5"></i> Logout</a>
  </div>

  <!-- Main content -->
  <div class="pt-24 px-6 transition-all duration-300" id="mainContent">

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
        <a href="today_sales.php" class="flex flex-col items-center">
          <i class="fas fa-shopping-bag text-2xl mb-2"></i>
          <div class="text-sm text-gray-200">Today's Sales</div>
          <div class="text-lg font-semibold">₹<?php echo number_format($today_sales, 2); ?></div>
        </a>
      </div>
      <div class="bg-teal-700 text-white rounded-lg shadow p-5 flex flex-col items-center hover:scale-105 transition">
        <a href="total_sales.php" class="flex flex-col items-center">
          <i class="fas fa-chart-line text-2xl mb-2"></i>
          <div class="text-sm text-gray-200">Total Sales</div>
          <div class="text-lg font-semibold">₹<?php echo number_format($total_sales, 2); ?></div>
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

    <!-- Tables side by side -->
    <div class="flex flex-col lg:flex-row gap-6">

      <!-- Top 10 Products -->
      <div class="flex-1 bg-white rounded-xl shadow p-4">
        <h3 class="text-lg font-semibold mb-3">Top 10 Products</h3>

        <!-- Search input with icon -->
        <div class="relative mb-3">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          <input type="text" id="productSearch" class="pl-10 pr-3 py-2 border rounded w-full"
            placeholder="Search for products">
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-center border-collapse text-xs sm:text-sm lg:text-base">
            <thead class="bg-teal-700 text-white">
              <tr>
                <th class="px-3 py-2">Product Name</th>
                <th class="px-3 py-2">Total Ordered</th>
                <th class="px-3 py-2">Action</th>
              </tr>
            </thead>
            <tbody id="topProductsBody">
              <?php if ($result_top_products && $result_top_products->num_rows > 0):
                while ($row = $result_top_products->fetch_assoc()): ?>
                  <tr class="border-b hover:bg-gray-100">
                    <td class="px-3 py-2"><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td class="px-3 py-2"><?php echo $row['total_ordered']; ?></td>
                    <td class="px-3 py-2">
                      <a href="view_product.php?name=<?php echo urlencode($row['product_name']); ?>"
                        class="bg-[#2bcdaa] text-white px-3 py-1 rounded hover:bg-teal-600 transition flex items-center justify-center">
                        <i class="fas fa-eye"></i>
                        <span class="hidden md:inline ml-1">View</span>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; else: ?>
                <tr>
                  <td colspan="3" class="text-center py-2">No products found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top 10 Royal Customers -->
      <div class="flex-1 bg-white rounded-xl shadow p-4">
        <h3 class="text-lg font-semibold mb-3">Top 10 Royal Customers</h3>

        <!-- Search input with icon -->
        <div class="relative mb-3">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          <input type="text" id="customerSearch" class="pl-10 pr-3 py-2 border rounded w-full"
            placeholder="Search for customers">
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-center border-collapse text-xs sm:text-sm lg:text-base">
            <thead class="bg-teal-700 text-white">
              <tr>
                <th class="px-3 py-2">Customer Name</th>
                <th class="px-3 py-2">Order Count</th>
                <th class="px-3 py-2">Action</th>
              </tr>
            </thead>
            <tbody id="topCustomersBody">
              <?php if ($result_top_customers && $result_top_customers->num_rows > 0):
                while ($row = $result_top_customers->fetch_assoc()): ?>
                  <tr class="border-b hover:bg-gray-100">
                    <td class="px-3 py-2"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="px-3 py-2"><?php echo $row['order_count']; ?></td>
                    <td class="px-3 py-2">
                      <a href="view_customer.php?id=<?php echo $row['id']; ?>"
                        class="bg-[#2bcdaa] text-white px-3 py-1 rounded hover:bg-teal-600 transition flex items-center justify-center">
                        <i class="fas fa-eye"></i>
                        <span class="hidden md:inline ml-1">View</span>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; else: ?>
                <tr>
                  <td colspan="3" class="text-center py-2">No customers found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Low Stock Popup -->
      <?php if (!empty($lowStockItems)): ?>
        <div id="lowStockPopup">
          <strong>⚠️ Low Stock Alert!</strong>
          <ul>
            <?php foreach ($lowStockItems as $item): ?>
              <li><?= htmlspecialchars($item['product_name']) ?> (<?= htmlspecialchars($item['weight']) ?>) - Qty:
                <?= htmlspecialchars($item['quantity']) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <script>
          setTimeout(() => { document.getElementById('lowStockPopup').style.display = 'none'; }, 8000);
        </script>
      <?php endif; ?>
    </div>
  </div>


  <script>
    // Drawer toggle
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const drawer = document.getElementById('drawer');
    const body = document.body;

    // hamburgerBtn.addEventListener('click', () => {
    //     drawer.classList.toggle('open');
    //     body.classList.toggle('drawer-open');
    // });
    hamburgerBtn.addEventListener('click', () => {
      drawer.classList.toggle('-translate-x-full');
      mainContent.classList.toggle('ml-64');
    });

    // Search functionality
    document.getElementById('productSearch').addEventListener('keyup', function () {
      let input = this.value.toLowerCase();
      document.querySelectorAll('#topProductsBody tr').forEach(row => {
        row.style.display = row.cells[0].textContent.toLowerCase().includes(input) ? '' : 'none';
      });
    });
    document.getElementById('customerSearch').addEventListener('keyup', function () {
      let input = this.value.toLowerCase();
      document.querySelectorAll('#topCustomersBody tr').forEach(row => {
        row.style.display = row.cells[0].textContent.toLowerCase().includes(input) ? '' : 'none';
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>