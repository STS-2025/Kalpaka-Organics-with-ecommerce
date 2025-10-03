<?php
$conn = mysqli_connect("localhost", "root", "", "kcpl");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "update_product") {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $image = $_POST['image'];
        $description = $_POST['description'];

        $sql = "UPDATE products SET name=?, image=?, description=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $image, $description, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } elseif ($action === "update_variant") {
        $variant_id = intval($_POST['variant_id']);
        $weight = $_POST['weight'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];

        $sql = "UPDATE product_variants SET weight=?, price=?, quantity=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdii", $weight, $price, $quantity, $variant_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } elseif ($action === "delete_variant") {
        $variant_id = intval($_POST['variant_id']);

        $sql = "DELETE FROM product_variants WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $variant_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } elseif ($action === "add_variant") {
        $product_id = intval($_POST['product_id']);
        $weight = $_POST['weight'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];

        $sql = "INSERT INTO product_variants (product_id, weight, price, quantity) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isdi", $product_id, $weight, $price, $quantity);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);

// Redirect back to product manage page
header("Location: ui5manageproduct.php?id=" . ($_POST['id'] ?? $_POST['product_id']));
exit;

