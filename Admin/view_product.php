<?php
/*session_start();
// Database connection details
$conn = new mysqli("localhost", "root", "", "kcpl");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the product name from the URL, decoding spaces
$product_name_from_url = urldecode($_GET['name'] ?? '');

// --- CORRECTED QUERY ---
// Fetch product details and price/quantity from product_variants
$product_query = "SELECT p.*, pv.price, pv.quantity 
                  FROM products p
                  LEFT JOIN product_variants pv ON p.id = pv.product_id
                  WHERE p.name = ? LIMIT 1";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("s", $product_name_from_url);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit;
}

$stmt->close();
$conn->close();

// Since images are in htdocs root
$image_base_path = 'http://localhost/ko_test_mith/php/admin/uploads/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
        .product-card {
            background-color: var(--white);
            border: 1px solid var(--subtle-gray);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: center;
        }
        .product-image-container {
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .product-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
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
        }
        .back-button {
            background-color: var(--accent-blue);
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
    <h1>Product Details</h1>
    <div class="product-card">
        <div class="product-image-container">
            <?php
            // Check if 'image' key exists before using it
            $images = isset($product['image']) ? explode(',', $product['image']) : ['default.jpg'];
            $first_image = trim($images[0]);
            $image_path = $image_base_path . $first_image;
            ?>
            <img
                src="<?php echo htmlspecialchars($image_path); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                onerror="this.onerror=null;this.src='https://placehold.co/400x200/e9ecef/2c3e50?text=Image+Not+Found';">
        </div>
        
        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
        
        <p class="card-text">Price: ₹<?php echo isset($product['price']) ? number_format($product['price'], 2) : 'N/A'; ?></p>
        <p class="card-text">Description: <?php echo htmlspecialchars($product['description']); ?></p>
        <p class="card-text">Available Stock: <?php echo isset($product['quantity']) ? $product['quantity'] : 'N/A'; ?></p>

        <a href="Admin_home.php" class="btn back-button mt-4">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>*/



session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "kcpl");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product name from URL
$product_name_from_url = urldecode($_GET['name'] ?? '');
if (empty($product_name_from_url)) {
    echo "Invalid product.";
    exit;
}

// Fetch product + variants
$product_query = "
    SELECT p.id, p.name, p.description, p.image,
           v.id AS variant_id, v.weight, v.price, v.quantity
    FROM products p
    LEFT JOIN product_variants v ON p.id = v.product_id
    WHERE p.name = ?
";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("s", $product_name_from_url);
$stmt->execute();
$result = $stmt->get_result();

$product = null;
$product_variants = [];

while ($row = $result->fetch_assoc()) {
    if (!$product) {
        $product = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'image' => $row['image']
        ];
    }
    if ($row['variant_id']) {
        $product_variants[] = [
            'id' => $row['variant_id'],
            'weight' => $row['weight'],
            'price' => $row['price'],
            'quantity' => $row['quantity']
        ];
    }
}

$stmt->close();
$conn->close();

if (!$product) {
    echo "Product not found.";
    exit;
}

// ✅ Images base path
$image_base_path = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --dark-charcoal: #2c3e50;
            --light-gray: #f8f9fa;
            --white: #ffffff;
            --accent-blue: #4169E1;
        }
        body {
            background-color: var(--light-gray);
            font-family: 'Poppins', sans-serif;
        }
        .main-container {
            margin-top: 50px;
            max-width: 700px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            background-color: var(--white);
            border-radius: 12px;
            margin-left: auto;
            margin-right: auto;
        }
        .product-image-container {
            height: 250px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .product-image-container img {
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        .back-button {
            background-color: var(--accent-blue);
            border: none;
            color: var(--white);
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #3157b1;
            color: var(--white);
        }
    </style>
</head>
<body>

<div class="main-container">
    <h1 class="text-center mb-4">Product Details</h1>
    <div class="product-card text-center">
        <div class="product-image-container">
            <?php 
            $images = explode(',', $product['image']);
            $first_image = trim($images[0]);
            $image_path = $image_base_path . $first_image;
            ?>
            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 onerror="this.onerror=null;this.src='https://placehold.co/400x200/e9ecef/2c3e50?text=Image+Not+Found';">
        </div>
        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
    </div>

    <h5 class="mt-4">Available Variants</h5>
    <?php if (!empty($product_variants)): ?>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Weight</th>
                    <th>Price (₹)</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($product_variants as $variant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($variant['weight']); ?></td>
                    <td><?php echo number_format($variant['price'], 2); ?></td>
                    <td><?php echo $variant['quantity']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No variants available for this product.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="Admin_home.php" class="back-button">Back to Dashboard</a>
    </div>
</div>

</body>
</html>
