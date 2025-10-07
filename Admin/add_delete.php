<?php
/*$conn = mysqli_connect("localhost", "root", "", "kcpl");
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
?>*/


require_once "../php/db.php";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $did = $_POST['id'] ?? '';

    if (!empty($did)) {
        $did = intval($did); // Sanitize ID

        // 1️⃣ First, fetch the image path from DB
        $selectSql = "SELECT image FROM products WHERE id = $did LIMIT 1";
        $result = mysqli_query($conn, $selectSql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $imagePath ='../'.$row['image']; // e.g., 'uploads/product1.jpg'

            // 2️⃣ Attempt to delete the image file from the server
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath); // deletes the file
            }

            // 3️⃣ Now delete the product from the DB
            $deleteSql = "DELETE FROM products WHERE id = $did LIMIT 1";
            if (mysqli_query($conn, $deleteSql)) {
                echo "Record and image deleted successfully";
            } else {
                echo "Failed to delete record: " . mysqli_error($conn);
            }
        } else {
            echo "Product not found";
        }
    } else {
        echo "Invalid ID";
    }
} else {
    echo "Invalid request method";
}

 $conn->close();?>
?>

