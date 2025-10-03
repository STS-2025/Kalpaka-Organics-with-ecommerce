 <?php
function opencon() {
    $servername ="localhost";
    $username ="root";
    $password ="";
    $dbname ="kcpl"; // Change this to your actual database name

    // Create connection
    $link = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $link;
}
?>