<?php
// Database connection
require_once "../php/db.php";
//$conn = opencon();

// Pagination setup
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch products with variants
$sql = "SELECT p.id, p.name, p.description, p.image,
               v.id AS variant_id, v.weight, v.price, v.quantity
        FROM products p
        LEFT JOIN product_variants v ON p.id = v.product_id
        ORDER BY p.name ASC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pid = $row['id'];
    if (!isset($products[$pid])) {
        $fileName = basename(trim($row['image']));
        $products[$pid]['info'] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'image' => '../' .$row['image']
        ];
        $products[$pid]['variants'] = [];
    }
    if (!empty($row['variant_id'])) {
        $products[$pid]['variants'][] = [
            'id' => $row['variant_id'],
            'weight' => $row['weight'],
            'price' => $row['price'],
            'quantity' => $row['quantity']
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            transition: margin-left 0.3s ease;
        }
        .content {
            margin: 90px auto 30px auto;
            width: 95%;
            max-width: 1200px;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #00509e;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
        }
        td {
            font-size: 14px;
            color: #333;
        }
        tr:nth-child(even) td { background-color: #f9f9f9; }
        tr:hover td { background-color: #e6f0ff; }

        /* Buttons */
        .update-btn, .delete-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .update-btn { background-color: #28a745; color: #fff; }
        .update-btn:hover { background-color: #218838; }
        .delete-btn { background-color: #dc3545; color: #fff; }
        .delete-btn:hover { background-color: #c82333; }

        /* Product images */
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* Drawer open effect */
        body.drawer-open .content {
            margin-left: 250px; /* adjust based on drawer width */
            width: calc(100% - 250px);
        }
        @media (max-width: 768px) {
            body.drawer-open .content {
                margin-left: 0;
                width: 100%;
            }
            .content table {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
            }
            table th, table td {
                padding: 6px 8px;
                font-size: 12px;
            }
            .product-img {
                width: 40px;
                height: 40px;
            }
            .update-btn, .delete-btn {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<?php include 'navbar_admin.php'; ?>

<div class="content">
    <h1 style="text-align:center; margin-bottom:30px;">

    <table>
        <thead>
            <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Image</th>
                <th>Description</th>
                <th>Variants (Weight - Price - Qty)</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sno = $offset + 1;
            foreach ($products as $prod):
                $info = $prod['info'];
                $variants = $prod['variants'];
            ?>
            <tr>
                <td><?= $sno ?></td>
                <td><?= htmlspecialchars($info['name']) ?></td>
                <td><img src="<?= htmlspecialchars($info['image']) ?>" class="product-img" alt="<?= htmlspecialchars($info['name']) ?>"></td>
                <td><?= htmlspecialchars($info['description']) ?></td>
                <td>
                    <?php
                    if (!empty($variants)) {
                        foreach ($variants as $v) {
                            echo htmlspecialchars($v['weight']) . " - ₹" . htmlspecialchars($v['price']) . " ({$v['quantity']} left)<br>";
                        }
                    } else { echo "No variants"; }
                    ?>
                </td>
                <td><a href="addview.php?id=<?= $info['id'] ?>"><button class="update-btn">Update</button></a></td>
                <td><button class="delete-btn delete1" data-id="<?= $info['id'] ?>">Delete</button></td>
            </tr>
            <?php
            $sno++;
            endforeach;
            ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on("click", ".delete1", function() {
    var id = $(this).data("id");
    var row = $(this).closest("tr");
    if (confirm("Are you sure you want to delete this product?")) {
        $.post("add_delete.php", { id: id }, function(resp) {
            row.remove();
        }).fail(function() {
            alert("Failed to delete product.");
        });
    }
});
</script>
</body>
</html>
