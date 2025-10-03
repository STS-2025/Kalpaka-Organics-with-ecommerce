 <?php
session_start();
$conn = new mysqli("localhost", "root", "", "kcpl");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user id from URL
$user_id = $_GET['id'] ?? '';
if (!$user_id) {
    echo "No customer selected.";
    exit;
}

// Fetch customer details with join to addresses
$customer_query = "
    SELECT u.id, u.name, u.email, a.phone, a.address_line1 AS address,
           (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS total_orders
    FROM users u
    LEFT JOIN addresses a ON u.id = a.user_id
    WHERE u.id = ?
";
$stmt = $conn->prepare($customer_query);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-charcoal: #2c3e50;
            --royal-blue: #003366;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --accent-blue: #4169E1;
            --subtle-gray: #e9ecef;
            --border-color: #dee2e6;
        }
        body {
            background-color: var(--light-gray);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        .main-container {
            margin-top: 50px;
            margin-bottom: 50px;
            max-width: 600px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            background-color: var(--white);
            border-radius: 12px;
            margin-left: auto;
            margin-right: auto;
        }
        h1 {
            text-align: center;
            color: var(--dark-charcoal);
            font-weight: 600;
            margin-bottom: 30px;
        }
        .customer-card {
            background-color: var(--white);
            border: 1px solid var(--subtle-gray);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 10px;
        }
        .card-text {
            color: #555;
            font-size: 1rem;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .back-button {
            background-color: #003366;
            border: none;
            color: var(--white);
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #3157b1;
            color: var(--white);
        }
    </style>
</head>
<body>

<div class="main-container">
    <h1>Customer Details</h1>
    <div class="customer-card">
        <h5 class="card-title"><?php echo htmlspecialchars($customer['name']); ?></h5>
        <p class="card-text">Email: <?php echo htmlspecialchars($customer['email']); ?></p>
        <p class="card-text">Phone: <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
        <p class="card-text">Address: <?php echo htmlspecialchars($customer['address'] ?? 'N/A'); ?></p>
        <p class="card-text">Total Orders: <?php echo $customer['total_orders'] ?? 0; ?></p>
    </div>
    
    <div class="text-center mt-4">
        <a href="ui5manageusers.php" class="btn back-button">Back to manage users</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html