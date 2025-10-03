<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// Database connection
$conn = new mysqli("localhost", "root", "", "kcpl");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle delivery confirmation
if (isset($_POST['confirm_delivery'])) {
    $order_id = intval($_POST['order_id']);
    $courier_service = $_POST['courier_service'];
    $delivered_on = date('Y-m-d H:i:s');

    $check = $conn->prepare("SELECT order_status FROM orders WHERE id=?");
    $check->bind_param("i", $order_id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res && strtolower($res['order_status']) === 'cancelled') {
        $_SESSION['error_message'] = "❌ Cannot deliver a cancelled order.";
        header("Location: order_status.php"); exit();
    }

    $update = $conn->prepare("UPDATE orders SET delivery_status='Delivered', delivered_on=?, courier_service=? WHERE id=?");
    $update->bind_param("ssi", $delivered_on, $courier_service, $order_id);
    $update->execute();

    $fetch = $conn->prepare("SELECT u.name AS username,o.total_amount FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
    $fetch->bind_param("i", $order_id);
    $fetch->execute();
    $data = $fetch->get_result()->fetch_assoc();

    $insert = $conn->prepare("INSERT INTO delivery_details (delivered_on,username,total_amount,courier_service) VALUES (?,?,?,?)");
    $insert->bind_param("ssds", $delivered_on,$data['username'],$data['total_amount'],$courier_service);
    $insert->execute();

    $_SESSION['success_message'] = "✅ Delivery confirmed successfully!";
    header("Location: order_status.php"); exit();
}

// Fetch pending orders
$query = "
SELECT o.id AS order_id,u.name AS username,o.total_amount,o.payment_status,a.full_name,a.phone,
CONCAT(a.address_line1,', ',IFNULL(a.address_line2,''),', ',a.city,', ',a.state,' - ',a.postal_code) AS address,
o.courier_service,o.order_status
FROM orders o
JOIN users u ON o.user_id=u.id
JOIN addresses a ON o.address_id=a.id
WHERE o.delivery_status='Pending' AND o.order_status!='Cancelled'
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Status</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
/* Body */
body { font-family: "Segoe UI", Arial, sans-serif; transition: margin-left .3s; background:#f4f6f9; padding-top:60px; }
body.drawer-open { margin-left:230px; }
@media (max-width:768px){ body.drawer-open{ margin-left:0; } }

/* Navbar */
.navbar {
  display:flex; align-items:center; justify-content:space-between;
  height:60px; padding:0 15px; background:#00509e; color:#fff;
  position:fixed; top:0; left:0; width:100%; z-index:2000;
  box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.hamburger{ font-size:22px; background:none; border:none; color:#fff; cursor:pointer; }
.brand{ flex:1; text-align:center; font-weight:600; font-size:20px; }
.welcome{ font-weight:600; color:#FFD700; }

/* Drawer */
.drawer {
  position:fixed; top:60px; left:-230px; width:230px; height:100%; background:#00509e;
  color:#fff; box-shadow:2px 0 8px rgba(0,0,0,.1); transition:left .3s; z-index:1500;
  display:flex; flex-direction:column;
}
.drawer.open{ left:0; }
.drawer .title { padding:15px; font-weight:700; font-size:19px; background:#004080; border-bottom:1px solid rgba(255,255,255,0.2); }
.drawer a{
  padding:12px 16px;
  display:block;
  text-decoration:none;
  color:#fff;
  font-weight:700;       /* a bit bolder */
  font-size:20px;        /* make text bigger like first file */
  line-height:1.5;       /* better spacing */
}

.drawer a:hover{ background:#cce0ff; color:#003366; border-radius:6px; }

/* Page title */
h2{ text-align:center; margin-top:30px; font-weight:600; }

/* Card */
.card{ margin-top:20px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-radius:12px; padding:20px; }

/* Print button above table */
.print-top-right{ text-align:right; margin-bottom:10px; }
.btn-print{ padding:5px 10px; font-size:0.9rem; font-weight:500; }

/* Table with rounded corners */
.table-container { overflow-x:auto; border-radius:12px; }
.table { border-radius:12px !important; overflow:hidden; }
.table-primary th{ background-color:#00509e !important; color:#fff !important; text-align:center; }
.table td, .table th{ text-align:center; vertical-align:middle; }
.table tbody tr:hover{ background:#f1f5ff; }

/* Print styles */
@media print{
  body *{ visibility:hidden; }
  .card, .card *{ visibility:visible; }
  .navbar,.drawer,.btn{ display:none; }
  table, th, td{ border:1px solid #000; border-collapse:collapse; font-size:10pt; }
  th, td{ padding:4px 6px; }
  table th:last-child,table td:last-child,
  table th:nth-last-child(2),table td:nth-last-child(2){ display:none; }
}

@media (max-width: 768px) {
  .table-container {
    width: 100%;
    overflow-x: auto;
  }
  .table td, .table th {
    font-size: 12px; /* slightly smaller for mobile */
    padding: 6px 8px;
  }
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
 <a href="Admin_home.php"><i class="fas fa-book me-2"></i>Records</a>
 <a href="ui5manageusers.php"><i class="fas fa-users me-2"></i>Users</a>
 <a href="ui5manageproduct.php"><i class="fas fa-box me-2"></i>Products</a>
 <a href="addnewproduct.php"><i class="fas fa-plus me-2"></i>Add Product</a>
 <a href="payment_check.php"><i class="fas fa-university me-2"></i>Payment</a>
 <a href="delivery.php"><i class="fas fa-truck me-2"></i>Delivery</a>
 <a href="/ko_test_mith/html/shop.html"><i class="fas fa-sign-out me-2"></i>Logout</a>
</div>

<div class="container">


 <!-- Print Button -->
 <div class="print-top-right">
   <button type="button" class="btn btn-primary btn-print" onclick="window.print()">
     <i class="fa-solid fa-print"></i> Print
   </button>
 </div>

 <?php if(isset($_SESSION['success_message'])): ?>
   <div class="alert alert-success alert-dismissible fade show mt-2">
     <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
 <?php endif; ?>

 <div class="card p-3">
   <div class="table-container">
     <table class="table table-bordered table-striped mb-0">
       <thead class="table-primary">
         <tr>
           <th>Order ID</th><th>Username</th><th>Total Amount</th>
           <th>Address</th><th>Phone</th><th>Payment Status</th>
           <th>Courier Service</th><th>Action</th>
         </tr>
       </thead>
       <tbody>
       <?php while($row=$result->fetch_assoc()): ?>
         <tr>
           <form method="POST">
           <td><?=htmlspecialchars($row['order_id'])?></td>
           <td><?=htmlspecialchars($row['username'])?></td>
           <td><?=htmlspecialchars($row['total_amount'])?></td>
           <td><?=htmlspecialchars($row['address'])?></td>
           <td><?=htmlspecialchars($row['phone'])?></td>
           <td><?=htmlspecialchars($row['payment_status'])?></td>
           <td>
             <select name="courier_service" class="form-select form-select-sm">
               <option value="Courier Service">Courier Service</option>
             </select>
           </td>
           <td>
             <input type="hidden" name="order_id" value="<?=$row['order_id']?>">
             <button type="submit" name="confirm_delivery" class="btn btn-success btn-sm">Confirm</button>
           </td>
           </form>
         </tr>
       <?php endwhile; ?>
       </tbody>
     </table>
   </div>
 </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
 const hamburgerBtn=document.getElementById('hamburgerBtn');
 const drawer=document.getElementById('drawer');
 hamburgerBtn.addEventListener('click',()=>{
   drawer.classList.toggle('open');
   document.body.classList.toggle('drawer-open');
 });
</script>
</body>
</html>
<?php $conn->close(); ?>
