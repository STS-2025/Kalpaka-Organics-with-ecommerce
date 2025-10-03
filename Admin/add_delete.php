<?php
$conn = mysqli_connect("localhost", "root", "", "kcpl");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $did = $_POST['id'] ?? '';

    if (!empty($did)) {
        $did = intval($did); // make sure it's a number
        $sql = "DELETE FROM products WHERE id = $did LIMIT 1";

        if (mysqli_query($conn, $sql)) {
            echo "Record deleted successfully";
        } else {
            echo "Failed to delete record: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid ID";
    }
} else {
    echo "Invalid request method";
}

mysqli_close($conn);
?>
