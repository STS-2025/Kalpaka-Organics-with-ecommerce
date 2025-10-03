 <?php
$conn=mysqli_connect("localhost", "root", "", "kcpl");

$id=$_POST['id'];
$username=$_POST['username'];
$email=$_POST['email'];
$phone=$_POST['phone'];
$address=$_POST['address'];
$password=$_POST['password'];
$confirmpassword=$_POST['confirmpassword'];
$sql="update page set username='$username',email='$email',phone='$phone',address='$address',password='$password',confirmpassword='$confirmpassword' where id='$id'";
echo $sql;
if (mysqli_query($conn,$sql)){
	echo "records added successfully";
}
header('location:ui5manageusers.php');
?>