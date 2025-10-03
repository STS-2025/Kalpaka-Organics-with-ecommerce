 <?php
error_reporting(0);
?>
<?php
try
{

$conn=mysqli_connect("localhost", "root", "", "kcpl");
$did=$_REQUEST['id'];
$sql="DELETE from page where id='$did' LIMIT 1";
if (mysqli_query($conn,$sql)){
	echo"records deleted successfully";
}
}
catch(PDOException $e)
{
	echo "There is some problem in connection:" . $e->getMessage();
}
