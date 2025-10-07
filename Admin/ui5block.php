 <?php
require_once "../php/db.php";


$id = $_POST['id'];
$is_blocked = $_POST['is_blocked'];

// Update in USERS table (not PAGE)
$sql = "UPDATE users SET is_blocked = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $is_blocked, $id);

if (mysqli_stmt_execute($stmt)) {
    echo $is_blocked ? "User blocked successfully." : "User unblocked successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
$conn->close();
?>