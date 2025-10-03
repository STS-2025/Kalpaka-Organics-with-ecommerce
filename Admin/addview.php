<?php
// ✅ DB connection
$conn = mysqli_connect("localhost", "root", "", "kcpl");
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id'])) {
  die("Product ID not set.");
}

$id = intval($_GET['id']);

// ✅ Fetch product info
$query = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$row = mysqli_fetch_assoc($result)) {
  die("Product not found.");
}
mysqli_stmt_close($stmt);

// ✅ Fetch variants
$variants = [];
$vquery = "SELECT * FROM product_variants WHERE product_id = ?";
$vstmt = mysqli_prepare($conn, $vquery);
mysqli_stmt_bind_param($vstmt, 'i', $id);
mysqli_stmt_execute($vstmt);
$vresult = mysqli_stmt_get_result($vstmt);
while ($vrow = mysqli_fetch_assoc($vresult)) {
  $variants[] = $vrow;
}
mysqli_stmt_close($vstmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Update Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .container {
      max-width: 900px;
      margin-top: 40px;
    }
    .card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 18px rgba(0,0,0,0.06);
      margin-bottom: 30px;
    }
    .card-header {
      background: #00509e;
      color: #fff;
      font-weight: 500;
      font-size: 1.1rem;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }
    label {
      font-weight: 500;
    }
    .btn-primary {
      background-color: #00509e;
      border: none;
    }
    .btn-primary:hover {
      background-color: #3157b1;
    }
    .btn-danger {
      background-color: #d63031;
      border: none;
    }
    .btn-danger:hover {
      background-color: #e55039;
    }
    hr {
      margin: 1.5rem 0;
    }
  </style>
</head>
<body>

<div class="container">
  <!-- ✅ Update Product -->
  <div class="card">
    <div class="card-header">
      <i class="fa fa-edit"></i> Update Product
    </div>
    <div class="card-body">
      <form action="addupdate.php" method="POST">
        <input type="hidden" name="action" value="update_product">
        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']); ?>">

        <div class="mb-3">
          <label for="name">Name</label>
          <input type="text" class="form-control" name="name" id="name"
                 value="<?= htmlspecialchars($row['name']); ?>" required>
        </div>

        <div class="mb-3">
          <label for="image">Image URL</label>
          <input type="text" class="form-control" name="image" id="image"
                 value="<?= htmlspecialchars($row['image']); ?>" required>
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <textarea class="form-control" name="description" id="description" rows="3" required><?= htmlspecialchars($row['description']); ?></textarea>
        </div>
<div class="d-flex justify-content-center mt-3">
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save"></i> Update Product
        </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Manage Variants -->
  <div class="card">
    <div class="card-header">
      <i class="fa fa-box"></i> Manage Variants
    </div>
    <div class="card-body">
      <?php if (empty($variants)): ?>
        <p class="text-muted">No variants yet for this product.</p>
      <?php endif; ?>

      <?php foreach ($variants as $variant): ?>
        <div class="border rounded p-3 mb-4">
          <!-- Update Variant -->
          <form action="addupdate.php" method="POST" class="mb-2">
            <input type="hidden" name="action" value="update_variant">
            <input type="hidden" name="variant_id" value="<?= $variant['id']; ?>">

            <div class="row g-2">
              <div class="col-md-4">
                <label>Weight</label>
                <input type="text" class="form-control" name="weight" value="<?= htmlspecialchars($variant['weight']); ?>" required>
              </div>
              <div class="col-md-4">
                <label>Price</label>
                <input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars($variant['price']); ?>" required>
              </div>
              <div class="col-md-4">
                <label>Quantity</label>
                <input type="number" class="form-control" name="quantity" value="<?= htmlspecialchars($variant['quantity']); ?>" required>
              </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
<button type="submit" class="btn btn-primary btn-sm mt-3">
  <i class="fa fa-save"></i> Update Variant
</button>
 </div>

          </form>

          <!-- Delete Variant (separate form, same width) -->
          <form action="addupdate.php" method="POST" onsubmit="return confirm('Delete this variant?');">
            <input type="hidden" name="action" value="delete_variant">
            <input type="hidden" name="variant_id" value="<?= $variant['id']; ?>">
            <div class="d-flex justify-content-center mt-3">
           <button type="submit" class="btn btn-danger btn-sm mt-3">
  <i class="fa fa-trash"></i> Delete Variant
</button>
 </div>
          </form>
        </div>
      <?php endforeach; ?>

      <!-- ✅ Add Variant -->
      <h6 class="mt-4 fw-semibold">Add New Variant</h6>
      <form action="addupdate.php" method="POST">
        <input type="hidden" name="action" value="add_variant">
        <input type="hidden" name="product_id" value="<?= $row['id']; ?>">

        <div class="row g-2">
          <div class="col-md-4">
            <label>Weight</label>
            <input type="text" class="form-control" name="weight" required>
          </div>
          <div class="col-md-4">
            <label>Price</label>
            <input type="number" step="0.01" class="form-control" name="price" required>
          </div>
          <div class="col-md-4">
            <label>Quantity</label>
            <input type="number" class="form-control" name="quantity" required>
          </div>
        </div>
<div class="d-flex justify-content-center mt-3">
        <button type="submit" class="btn btn-primary mt-3">
          <i class="fa fa-plus"></i> Add Variant
        </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
