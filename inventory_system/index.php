<?php
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

<h2>Welcome to Inventory System</h2>

<a href="login.php">Login Here</a>

</body>
</html>