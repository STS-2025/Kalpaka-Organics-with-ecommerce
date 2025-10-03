
<?php
session_start();
include "toydb.php";
$link = opencon();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    mysqli_begin_transaction($link);

    try {
        // -------------------------------
        // 1. Handle Photo Uploads
        // -------------------------------
        $photos = [];
        $validTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
            $fileCount = count($_FILES['photos']['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = basename($_FILES['photos']['name'][$i]); // original filename
                $fileTmp  = $_FILES['photos']['tmp_name'][$i];
                $fileType = $_FILES['photos']['type'][$i];

                // Validate file type
                if (!in_array($fileType, $validTypes)) {
                    throw new Exception("Invalid image type: $fileName");
                }

                // ✅ Upload path in project/uploads/
                $uploadDir = __DIR__ . "/../uploads/";   // Absolute server path
                $relativePath = "uploads/" . $fileName;   // Path stored in DB

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uploadPath = $uploadDir . $fileName;

                // Optional: overwrite existing file with same name
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }

                // Move uploaded file
                if (!move_uploaded_file($fileTmp, $uploadPath)) {
                    throw new Exception("Failed to upload image: $fileName");
                }

                $photos[] = $relativePath; // store relative path instead of just filename
            }
        } else {
            throw new Exception("No images uploaded.");
        }

        // -------------------------------
        // 2. Collect Product Data
        // -------------------------------
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$name || !$description) {
            throw new Exception("All product fields are required.");
        }

        $photoList = implode(",", $photos); // store as comma-separated string

        // -------------------------------
        // 3. Insert Product
        // -------------------------------
        $stmt = $link->prepare("INSERT INTO products (image, name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $photoList, $name, $description);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting product: " . $stmt->error);
        }

        $product_id = $stmt->insert_id;
        $stmt->close();

        // -------------------------------
        // 4. Insert Variants
        // -------------------------------
        $weights  = $_POST['weights'] ?? [];
        $prices   = $_POST['prices'] ?? [];
        $quantity = $_POST['quantity'] ?? [];

        if (!empty($weights) && !empty($prices)) {
            if (count($weights) !== count($prices) || count($weights) !== count($quantity)) {
                throw new Exception("Variant data mismatch.");
            }

            $placeholders = [];
            $variant_values = [];
            $types = "";

            foreach ($weights as $i => $weight) {
                $variant_price = $prices[$i];
                $variant_qty   = $quantity[$i];

                if (!$weight || !$variant_price || !$variant_qty) continue;

                $placeholders[] = "(?, ?, ?, ?)";
                $types .= "isdi"; // product_id=int, weight=string, price=double, qty=int
                $variant_values[] = $product_id;
                $variant_values[] = $weight;
                $variant_values[] = (float)$variant_price;
                $variant_values[] = (int)$variant_qty;
            }

            if (!empty($placeholders)) {
                $sql = "INSERT INTO product_variants (product_id, weight, price, quantity) VALUES " . implode(", ", $placeholders);
                $variant_stmt = $link->prepare($sql);
                $variant_stmt->bind_param($types, ...$variant_values);

                if (!$variant_stmt->execute()) {
                    throw new Exception("Error inserting variants: " . $variant_stmt->error);
                }

                $variant_stmt->close();
            }
        }

        mysqli_commit($link);

        // ✅ Set session alert and redirect back
        $_SESSION['alert'] = "✅ Product and variants added successfully.";
        header("Location: addnewproduct.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($link);
        $_SESSION['alert'] = "❌ Error: " . $e->getMessage();
        header("Location: addnewproduct.php");
        exit;
    }
}

$link->close();
?>

