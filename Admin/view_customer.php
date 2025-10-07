<?php
session_start();
require_once "../php/db.php";

$user_id = $_GET['id'] ?? '';
if (!$user_id) {
    echo "No customer selected.";
    exit;
}

$query = "
    SELECT u.id, u.name, u.email, a.phone, a.address_line1 AS address,
           (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS total_orders
    FROM users u
    LEFT JOIN addresses a ON u.id = a.user_id
    WHERE u.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    echo "Customer not found.";
    exit;
}
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --dark-charcoal: #2c3e50;
      --accent-blue: #003366;
      --light-gray: #f8f9fa;
      --white: #ffffff;
      --border-color: #e0e0e0;
    }
    body {
      background-color: var(--light-gray);
      font-family: 'Poppins', sans-serif;
      color: #333;
      font-size: 14px;
    }
    .main-container {
      max-width: 600px;
      margin: 60px auto;
      background: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    h1 {
      text-align: center;
      font-weight: 600;
      color: var(--dark-charcoal);
      margin-bottom: 25px;
      font-size: 20px;
    }
    .customer-card {
      border: 1px solid var(--border-color);
      border-radius: 10px;
      padding: 20px 25px;
      background: var(--white);
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .customer-card h5 {
      font-size: 18px;
      font-weight: 600;
      color: var(--accent-blue);
      margin-bottom: 15px;
    }
    .customer-card p {
      margin-bottom: 8px;
      font-size: 14px;
      color: #555;
    }
    .label {
      font-weight: 500;
      color: #333;
    }
    .back-button {
      background-color: #00509e;
      color: var(--white);
      border: none;
      border-radius: 5px;
      padding: 8px 18px;
      font-size: 13px;
      margin-top: 20px;
      transition: background 0.3s ease;
    }
    .back-button:hover {
      background-color: #3157b1;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <h1>Customer Details</h1>
    <div class="customer-card">
      <h5><?php echo htmlspecialchars($customer['name']); ?></h5>
      <p><span class="label">Email:</span> <?php echo htmlspecialchars($customer['email']); ?></p>
      <p><span class="label">Phone:</span> <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
      <p><span class="label">Address:</span> <?php echo htmlspecialchars($customer['address'] ?? 'N/A'); ?></p>
      <p><span class="label">Total Orders:</span> <?php echo $customer['total_orders'] ?? 0; ?></p>
    </div>
    <div class="text-center">
      <a href="Admin_home.php" class="btn back-button mt-3">Back to Dashboard</a>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
