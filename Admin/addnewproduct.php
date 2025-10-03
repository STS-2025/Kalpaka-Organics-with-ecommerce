<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "kcpl");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Add New Product</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
:root {
    --primary-blue: #00509e;
    --primary-hover: #0b5ed7;
    --light-bg: #f4f6f8;
    --card-bg: #ffffff;
    --text-dark: #333;
    --input-border: #ccc;
}

body {
    margin:0;
    padding-top:70px;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    background-color: var(--light-bg);
    color: var(--text-dark);
}

h1, h2 {
    text-align: center;
    color: var(--primary-blue);
    margin-bottom: 20px;
    font-weight: 700;
}

.alert {
    background-color:#28a745;
    color:white;
    padding:12px 16px;
    border-radius:8px;
    max-width:600px;
    margin:20px auto;
    position:relative;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

.alert button {
    position:absolute;
    top:8px; right:12px;
    background:none;
    border:none;
    color:white;
    font-weight:bold;
    cursor:pointer;
}

.card-box {
    background-color: var(--card-bg);
    max-width: 720px;
    margin: 60px auto;
    padding: 30px 25px;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

label {
    display:block;
    margin-bottom:6px;
    font-weight:600;
    font-size: 14px;
}

input, textarea {
    width:100%;
    padding:12px;
    border:1px solid var(--input-border);
    border-radius:8px;
    margin-bottom:15px;
    font-size:14px;
    box-sizing:border-box;
    transition: border 0.3s;
}

input:focus, textarea:focus {
    border-color: var(--primary-blue);
    outline: none;
}

textarea {
    resize: vertical;
}

#imagePreviewContainer {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:15px;
}

#imagePreviewContainer img {
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:8px;
    border:1px solid #ddd;
}

.variants h2 {
    font-size: 20px;
    margin-bottom: 20px;
}

.variant-row {
    display:grid;
    grid-template-columns:1fr 1fr 1fr auto;
    gap:10px;
    margin-bottom:10px;
    align-items:center;
}

@media(max-width:768px){
    .variant-row{
        grid-template-columns:1fr;
        gap: 10px;
    }
}

.add, button[type=submit] {
    background-color: var(--primary-blue);
    color: white;
    font-size: 15px;
    font-weight:600;
    padding: 12px 0;
    border-radius: 50px;
    width: 220px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: background 0.3s;
    margin: 20px auto 0;
}

.add:hover, button[type=submit]:hover {
    background-color: var(--primary-hover);
}

.remove-btn {
    background-color: #d9534f;
    color:white;
    font-weight:500;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.remove-btn:hover {
    background-color: #c9302c;
}
</style>
</head>
<body>
<?php include 'navbar_admin.php'; ?>

<?php if(isset($_SESSION['alert'])): ?>
<div class="alert">
  <?=htmlspecialchars($_SESSION['alert']);?>
  <button onclick="this.parentElement.style.display='none'">×</button>
</div>
<?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<div class="card-box">
    
    <form action="addnew_ins.php" method="POST" enctype="multipart/form-data">
        <label for="photos">Product Photos (Multiple):</label>
        <input type="file" id="photos" name="photos[]" accept="image/*" multiple required onchange="previewImages(event)">
        <div id="imagePreviewContainer"></div>

        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" placeholder="Enter product name" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" maxlength="100" placeholder="Enter a short description" required></textarea>

        <div class="variants">
            <h2>Product Variants</h2>
            <div id="variants-container">
                <div class="variant-row">
                    <input type="text" name="weights[]" placeholder="Weight (e.g. 1 Kg / 1 Ltr)" required>
                    <input type="number" step="0.01" name="prices[]" placeholder="Price (e.g. 25.00)" required>
                    <input type="number" name="quantity[]" placeholder="No. of Packages" required>
                    <button type="button" class="remove-btn" onclick="removeVariant(this)">Remove</button>
                </div>
            </div>
            <button type="button" class="add" onclick="addVariant()"><i class="fas fa-plus"></i> Add Another Variant</button>
        </div>

        <button type="submit"><i class="fas fa-plus"></i> Add Product</button>
    </form>
</div>

<script>
function addVariant() {
    const container = document.getElementById('variants-container');
    const div = document.createElement('div');
    div.className = 'variant-row';
    div.innerHTML = `
        <input type="text" name="weights[]" placeholder="Weight (e.g. 1 Kg / 1 Ltr)" required>
        <input type="number" step="0.01" name="prices[]" placeholder="Price (e.g. 25.00)" required>
        <input type="number" name="quantity[]" placeholder="No. of Packages" required>
        <button type="button" class="remove-btn" onclick="removeVariant(this)">Remove</button>`;
    container.appendChild(div);
}

function removeVariant(btn){ btn.parentElement.remove(); }

function previewImages(event){
    const files = event.target.files;
    const container = document.getElementById('imagePreviewContainer');
    container.innerHTML = '';
    for(let i=0;i<files.length;i++){
        const reader = new FileReader();
        reader.onload = function(e){
            const img = document.createElement('img');
            img.src = e.target.result;
            container.appendChild(img);
        }
        reader.readAsDataURL(files[i]);
    }
}
</script>
</body>
</html>
