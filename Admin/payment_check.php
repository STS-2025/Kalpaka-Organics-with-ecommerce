<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost","root","","kcpl");
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Bulk mark paid
if(!empty($_POST['paid_orders'])){
    $ids = implode(',', array_map('intval', $_POST['paid_orders']));
    $conn->query("UPDATE orders SET payment_status='paid' WHERE id IN ($ids)");
    $_SESSION['success_message']="✅ Selected orders marked as Paid.";
    header("Location: payment_check.php"); exit();
}

// Cancel order
if(isset($_POST['cancel_order'])){
    $id = intval($_POST['order_id']);
    $reason = trim($_POST['cancelled_reason']);
    if($reason==='') $reason='Cancelled due to delay of payment.';
    $stmt=$conn->prepare("UPDATE orders SET order_status='Cancelled', cancelled_reason=? WHERE id=?");
    $stmt->bind_param("si",$reason,$id);
    $stmt->execute();
    $_SESSION['success_message']="❌ Order #$id cancelled.";
    header("Location: payment_check.php"); exit();
}

$res = $conn->query("
    SELECT o.id AS order_id,
           u.name AS username,
           o.total_amount,
           o.payment_status,
           o.order_status,
           o.cancelled_reason,
           o.upi_id,
           o.payment_phone
    FROM orders o
    JOIN users u ON o.user_id=u.id
    ORDER BY o.id DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Check</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
body { font-family:"Segoe UI", Arial, sans-serif; background:#f4f6f9; padding-top:60px; transition: margin-left .3s; }

/* Navbar */
.navbar { display:flex; align-items:center; justify-content:space-between; height:60px; padding:0 15px; background:#00509e; color:#fff; position:fixed; top:0; left:0; width:100%; z-index:2000; box-shadow:0 2px 6px rgba(0,0,0,0.2);}
.hamburger{ font-size:22px; background:none; border:none; color:#fff; cursor:pointer; }
.brand{ flex:1; text-align:center; font-weight:600; font-size:20px; }
.welcome{ font-weight:600; color:#FFD700; }

/* Drawer */
.drawer { position:fixed; top:60px; left:-230px; width:230px; height:100%; background:#00509e; color:#fff; box-shadow:2px 0 8px rgba(0,0,0,.1); transition:left .3s; z-index:1500; display:flex; flex-direction:column; }
.drawer.open{ left:0; }
.drawer .title { padding:15px; font-weight:700; font-size:19px; background:#004080; border-bottom:1px solid rgba(255,255,255,0.2); }
.drawer a{ padding:12px 16px; display:block; text-decoration:none; color:#fff; font-weight:700; font-size:20px; line-height:1.5; }
.drawer a:hover{ background:#cce0ff; color:#003366; border-radius:6px; }

/* Content shift only */
#mainContent { transition: margin-left .3s; }
body.drawer-open #mainContent { margin-left:230px; }
@media (max-width:768px){ body.drawer-open #mainContent{ margin-left:0; } }

/* Page title */
h2{ text-align:center; margin-top:30px; font-weight:600; }

/* Card */
.card{ margin-top:20px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-radius:12px; padding:20px; }

/* Print button */
.print-top-right{ text-align:right; margin-bottom:10px; }
.btn-print{ padding:5px 10px; font-size:0.9rem; font-weight:500; }

/* Table */
.table-container{ border-radius:12px; overflow-x:auto; }
.table{ border-radius:12px !important; overflow:hidden; }
.table-primary th{ background:#00509e !important; color:#fff !important; text-align:center; }
.table td, .table th{text-align:center;
    vertical-align:middle;
    padding:4px 6px; /* thinner rows */ }
    .print-top-right {
    position: fixed;
    top: 70px;
    right: 20px;
    z-index: 3000;
}
.table tbody tr:hover{ background:#f1f5ff; }

/* Cancel reason column */
.cancel-reason-col{ width:180px; }

/* Print */
@media print{
    body *{ visibility:hidden; }
    #mainContent, #mainContent *{ visibility:visible; }
    .navbar,.drawer,.btn{ display:none; }
    table, th, td{ border:1px solid #000; border-collapse:collapse; font-size:10pt; }
    th, td{ padding:4px 6px; }
    table th:last-child,table td:last-child,
    table th:nth-last-child(2),table td:nth-last-child(2){ display:none; }
}
.refund-btn {
    background-color: #00509e; /* same as navbar */
    color: #fff;
    border: none;
}

.refund-btn:hover {
    background-color: #003366; /* darker on hover */
    color: #fff;
}

.table-container {
    border-radius: 12px;
    overflow-x:auto;
}

/* Responsive table for mobile */
@media (max-width: 768px) {
    .table td, .table th {
        padding: 4px 6px;       /* smaller padding for mobile */
        font-size: 12px;         /* smaller font */
    }

    .cancel-reason-col { width: auto; } /* allow shrink */
    .table-container { overflow-x:auto; }
}
</style>
</head>
<body>

<div class="navbar">
   <button class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
   <div class="brand">Kalpaka Organics</div>
   <div class="welcome">Welcome Admin 🙏</div>
</div>

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

<div id="mainContent" class="container">


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
         <form method="POST">
             <div class="d-flex justify-content-end mb-3">
                 <button type="submit" class="btn btn-success" name="mark_paid_btn">
                     <i class="fas fa-check-circle me-1"></i> Mark Paid
                 </button>
             </div>
             <table class="table table-bordered table-striped mb-0">
                 <thead class="table-primary">
                     <tr>
                         <th>Select</th>
                         <th>Order ID</th>
                         <th>Username</th>
                         <th>Payment Details</th>
                         <th>Total Amount</th>
                         <th>Payment Status</th>
                         <th>Order Status</th>
                         <th class="cancel-reason-col">Cancel/Reason</th>
                         <th>Action</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php while($row=$res->fetch_assoc()): ?>
                         <tr class="<?=($row['order_status']=='Cancelled')?'table-danger':''?>">
                             <td><?php if($row['order_status']!='Cancelled'): ?><input type="checkbox" name="paid_orders[]" value="<?=$row['order_id']?>"><?php endif; ?></td>
                             <td><?=htmlspecialchars($row['order_id'])?></td>
                             <td><?=htmlspecialchars($row['username'])?></td>
                             <td>
                                 <?php if(!empty($row['upi_id']) || !empty($row['payment_phone'])): ?>
                                     <strong>UPI:</strong> <?php echo htmlspecialchars($row['upi_id']); ?><br>
                                     <strong>Phone:</strong> <?php echo htmlspecialchars($row['payment_phone']); ?>
                                 <?php else: ?>
                                     <span class="text-muted">No details</span>
                                 <?php endif; ?>
                             </td>
                             <td><?=htmlspecialchars($row['total_amount'])?></td>
                             <td><?=htmlspecialchars($row['payment_status'])?></td>
                             <td><?=htmlspecialchars($row['order_status'])?></td>
                             <td class="cancel-reason-col">
                                 <?php if($row['order_status']!='Cancelled'): ?>
                                     <select class="form-select form-select-sm mb-1" id="reason_select_<?=$row['order_id']?>">
                                         <option value="">- Select reason -</option>
                                         <option value="Due to stock unavailability.">Due to stock unavailability.</option>
                                         <option value="Due to delivery issue.">Due to delivery issue.</option>
                                         <option value="Due to shipping issue.">Due to shipping issue.</option>
                                         <option value="Due to delay of your response while delivery.">Due to delay of your response while delivery.</option>
                                     </select>
                                     <textarea id="reason_text_<?=$row['order_id']?>" rows="2" class="form-control mb-1" placeholder="Type custom reason..."></textarea>
                                     <button type="button" class="btn btn-danger btn-sm w-100" onclick="setCancelReasonAndSubmit('<?=$row['order_id']?>')">Cancel</button>
                                 <?php else: ?>
                                     <?=htmlspecialchars($row['cancelled_reason'] ?? '')?>
                                 <?php endif; ?>
                             </td>

                             <td>
                                 <a class="btn btn-sm refund-btn"
                                     href="payment_details.php?order_id=<?=$row['order_id']?>&upi=<?=urlencode($row['upi_id'] ?? '')?>&phone=<?=urlencode($row['payment_phone'] ?? '')?>"
                                     >
                                     Refund
                                 </a>
                             </td>
                         </tr>
                     <?php endwhile; ?>
                 </tbody>
             </table>
         </form>
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

function setCancelReasonAndSubmit(orderId){
    const reasonSelect=document.getElementById('reason_select_'+orderId);
    const reasonTextarea=document.getElementById('reason_text_'+orderId);
    let reason=reasonSelect.value.trim() || reasonTextarea.value.trim();
    const form=document.querySelector('form');
    const hiddenId=document.createElement('input'); hiddenId.type='hidden'; hiddenId.name='order_id'; hiddenId.value=orderId;
    const hiddenReason=document.createElement('input'); hiddenReason.type='hidden'; hiddenReason.name='cancelled_reason'; hiddenReason.value=reason;
    const hiddenCancel=document.createElement('input'); hiddenCancel.type='hidden'; hiddenCancel.name='cancel_order'; hiddenCancel.value='1';
    form.appendChild(hiddenId); form.appendChild(hiddenReason); form.appendChild(hiddenCancel);
    form.submit();
}
</script>

</body>
</html>
<?php $conn->close(); ?>