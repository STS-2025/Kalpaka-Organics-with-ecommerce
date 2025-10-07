<?php
session_start();
require_once "../php/db.php";
$order_id = $_GET['order_id'] ?? '';
$upi = $_GET['upi'] ?? '';
$phone = $_GET['phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refund'])) {
   
    $id = intval($_POST['order_id']);

    // Secure SELECT query using prepared statement
    $stmt = $conn->prepare("SELECT payment_status , order_status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
      $payment_status = $row['payment_status'];
      $order_status = $row['order_status'];

        if ($payment_status === 'paid' && $order_status != 'Cancelled') {
            // Secure UPDATE query using prepared statement
            $update = $conn->prepare("UPDATE orders SET payment_status = 'refunded' WHERE id = ?");
            $update->bind_param("i", $id);
            $update->execute();
            $update->close();

            $_SESSION['success_message'] = "💸 Refund completed for Order #$id.";
        }else {
            $_SESSION['error_message'] = "⚠️ Refund not allowed. Either the payment was not completed or the order is cancelled.";
        }
    }
    $stmt->close();
     $conn->close();


    header("Location: payment_check.php");
    exit();
}
?>
<?php


if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      max-width: 500px;
      margin: 60px auto;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .card-header {
      background: #003366;
      color: #fff;
      font-weight: 600;
      font-size: 20px;
      text-align: center;
      border-radius: 12px 12px 0 0;
    }
    .detail-label {
      font-weight: 600;
      color: #555;
    }
    .btn-refund {
      background: #dc3545;
      color: #fff;
      font-weight: 600;
    }
    .btn-refund:hover {
      background: #c82333;
    }
    .btn-back {
      background: #6c757d;
      color: #fff;
      font-weight: 600;
    }
    .btn-back:hover {
      background: #5a6268;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      <i class="fas fa-money-bill-wave"></i> Payment Details for Order #<?php echo htmlspecialchars($order_id); ?>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <span class="detail-label"><i class="fas fa-university me-2"></i>UPI ID:</span><br>
        <?php echo htmlspecialchars($upi ?: 'N/A'); ?>
      </div>
      <div class="mb-4">
        <span class="detail-label"><i class="fas fa-phone-alt me-2"></i>Phone:</span><br>
        <?php echo htmlspecialchars($phone ?: 'N/A'); ?>
      </div>
      <form method="POST" class="d-flex justify-content-between">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
        <button type="submit" name="refund" class="btn btn-refund">
          <i class="fas fa-undo"></i> Refund
        </button>
        <a href="payment_check.php" class="btn btn-back">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </form>
    </div>
  </div>
</body>
</html>
