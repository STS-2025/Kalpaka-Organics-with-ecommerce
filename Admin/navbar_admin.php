

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Navbar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 0;
    padding-top: 60px;
    background: transparent; /* changed from #f4f6f9 */
    transition: margin-left 0.3s ease;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 60px;
    background-color: #00509e;
    color: #fff;
    padding: 0 20px;
    z-index: 2000;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}

.hamburger {
    font-size: 22px;
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.hamburger:hover {
    transform: scale(1.1);
}

.brand {
    font-weight: 600;
    font-size: 20px;
    flex: 1;
    text-align: center;
    color: #fff;
}

.welcome {
    font-weight: 600;
    color: #FFD700;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Drawer */
.drawer {
    position: fixed;
    top: 60px;
    left: -250px;
    width: 250px;
    height: 100%;
    background: #00509e;
    color: #fff;
    box-shadow: 4px 0 12px rgba(0,0,0,0.15);
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
    transition: left 0.3s ease;
    z-index: 1500;
    display: flex;
    flex-direction: column;
}

.drawer.open { left: 0; }

.drawer .title {
    padding: 16px;
    font-size: 22px;
    font-weight: 700;
    text-align: left; /* aligned left */
    border-bottom: 1px solid rgba(255,255,255,0.2);
    background: #004080;
    color: #fff; /* white text */
    border-top-right-radius: 12px;
}

.drawer a {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    color: #fff; /* white text */
    text-decoration: none;
    font-size: 18px;
    font-weight: 600;
    transition: all 0.2s ease;
    border-radius: 6px;
    margin: 4px 8px;
}

.drawer a i {
    font-size: 20px;
    width: 28px;
    color: #fff; /* white icons */
}

.drawer a:hover {
    background: #cce0ff;
    color: #003366;
    border-left: 4px solid #00509e;
    padding-left: 14px;
}

/* Content shift when drawer opens */
body.drawer-open #mainContent {
    margin-left: 250px;
}

@media (max-width: 768px) {
    body.drawer-open #mainContent {
        margin-left: 0;
    }
}

/* Main content */
#mainContent {
    padding: 20px;
    transition: margin-left 0.3s ease;
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <button class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
    <div class="brand">Kalpaka Organics</div>
    <div class="welcome">Welcome Admin 🙏</div>
</div>

<!-- Drawer -->
<div class="drawer" id="drawer">
    <div class="title">Menu</div>
    <a href="Admin_home.php"><i class="fas fa-book"></i>Records</a>
    <a href="ui5manageusers.php"><i class="fas fa-users"></i>Users</a>
    <a href="ui5manageproduct.php"><i class="fas fa-box"></i>Products</a>
    <a href="addnewproduct.php"><i class="fas fa-plus"></i>Add Product</a>
    <a href="payment_check.php"><i class="fas fa-university"></i>Payment</a>
    <a href="delivery.php"><i class="fas fa-truck"></i>Delivery</a>
    <a href="/ko_test_mith/html/shop.html"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>


<script>
const hamburgerBtn = document.getElementById('hamburgerBtn');
const drawer = document.getElementById('drawer');
const body = document.body;

hamburgerBtn.addEventListener('click', () => {
    drawer.classList.toggle('open');
    body.classList.toggle('drawer-open');
});
</script>

</body>
</html>
