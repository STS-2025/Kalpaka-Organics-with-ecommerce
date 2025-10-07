<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../php/db.php";


$delivery_stmt = $conn->prepare("SELECT id, delivered_on, username, total_amount, courier_service FROM delivery_details");
$delivery_stmt->execute();
$delivery_results = $delivery_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Details</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
body {
  font-family: "Segoe UI", Arial, sans-serif;
  background-color: #f4f6f9;
  padding-top: 70px;
  color: #333;
  transition: margin-left .3s ease;
}
body.drawer-open {
  margin-left: 230px; /* Desktop: push content aside */
}
@media (max-width: 768px) {
  body.drawer-open {
    margin-left: 0; /* On mobile, content doesn’t shift */
  }
}

/* Navbar */
.navbar {
  display: flex; justify-content: space-between; align-items: center;
  background:#00509e; color:white; height:60px; padding:0 20px;
  position:fixed; top:0; left:0; width:100%; z-index:1000;
  box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.navbar .brand { font-weight:700; font-size:20px; }
.navbar .hamburger { background:none; border:none; color:white; font-size:22px; cursor:pointer; }
.navbar .welcome { font-size:16px; color:#FFD700; font-weight:600; }

/* Drawer */
.drawer {
  position: fixed;
  top: 60px;
  left: -230px;
  width: 230px;
  height: calc(100vh - 60px);
  background: #00509e;
  box-shadow: 2px 0 8px rgba(0,0,0,0.08);
  transition: left .3s ease;
  z-index: 999;
  display: flex;
  flex-direction: column;
}
.drawer.open { left: 0; }
.drawer .title {
  font-weight: 700;
  padding: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.2);
  background: #004080;
  color: white;
}
.drawer .menu a {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 20px;
  color: white;
  font-weight: 600;
  text-decoration: none;
}
.drawer .menu a:hover {
  background: #336fbf;
  border-radius: 6px;
}
.drawer a:hover{ background:#cce0ff; color:#003366; border-radius:6px; }

/* Content Card */
.content-card {
  max-width:1200px; margin:20px auto; background:white;
  border-radius:12px; padding:20px;
  box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

/* Table */
.table-container { border-radius:12px; overflow-x:auto; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
.table thead th {
  background:#00509e; color:white; text-align:center;
  font-weight:600; border:none;
}
.table tbody tr:hover { background:#f1f5ff; transition:background .2s; }
.table td, .table th { text-align:center; vertical-align:middle; white-space:nowrap; }

/* Buttons */
.btn-primary {
  background: #00509e;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  color: #fff;
  transition: none;
}
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active {
  background: #00509e !important;
  color: #fff;
  box-shadow: none;
}

/* Print */
@media print {
  body * { visibility:hidden; }
  .content-card, .content-card * { visibility:visible; }
  .navbar, .drawer, .btn { display:none; }
  table, th, td { border:1px solid #000; border-collapse:collapse; font-size:10pt; }
  th, td { padding:4px; }
}
@media (max-width: 768px) {
  .table-container { overflow-x:auto; }
  .table td, .table th { font-size:12px; padding:4px 6px; }
  .btn-primary { padding:5px 10px; font-size:12px; }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <button class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
  <div class="brand">Kalpaka Organics</div>
  <div class="welcome">Welcome Admin 🙏</div>
</div>

<!-- Drawer -->
<div class="drawer" id="drawer">
  <div class="title">Menu</div>
  <nav class="menu">
    <a href="Admin_home.php"><i class="fas fa-book"></i> Records</a>
    <a href="ui5manageusers.php"><i class="fas fa-users"></i> Users</a>
    <a href="ui5manageproduct.php"><i class="fas fa-box"></i> Products</a>
    <a href="addnewproduct.php"><i class="fas fa-plus"></i> Add Product</a>
    <a href="payment_check.php"><i class="fas fa-university"></i> Payment</a>
    <a href="delivery.php"><i class="fas fa-truck"></i> Delivery</a>
    <a href="../html/shop.html"><i class="fas fa-sign-out-alt"></i> Log Out</a>
  </nav>
</div>

<!-- Content -->
<div class="content-card">
  <div class="d-flex flex-wrap justify-content-between mb-3">
    <a href="order_status.php" class="btn btn-primary mb-2">
      <i class="fas fa-arrow-right"></i> Go to Confirm Delivery
    </a>
    <button class="btn btn-primary mb-2" onclick="window.print()">
      <i class="fas fa-print"></i> Print
    </button>
  </div>
  <div class="table-container">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Delivered On</th>
          <th>Username</th>
          <th>Total Amount</th>
          <th>Courier Service</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $delivery_results->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['delivered_on']) ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= htmlspecialchars($row['total_amount']) ?></td>
          <td><?= htmlspecialchars($row['courier_service']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const hamburgerBtn = document.getElementById('hamburgerBtn');
const drawer = document.getElementById('drawer');

hamburgerBtn.addEventListener('click', () => {
  drawer.classList.toggle('open');
  document.body.classList.toggle('drawer-open');
});
</script>

</body>
</html>
<?php $conn->close();  ?>
