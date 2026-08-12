<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager']);

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../config/db.php");

if (isset($_POST['register'])) {

    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users(full_name, username, email, password, role)
            VALUES('$full_name','$username','$email','$password','$role')";

    if(mysqli_query($conn, $sql)){
        echo "User Registered Successfully!";
    }else{
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register User</title>
</head>
<body>

<h2>Register First User</h2>

<form method="POST">

    <input type="text" name="full_name" placeholder="Full Name" required><br><br>

    <input type="text" name="username" placeholder="Username" required><br><br>

    <input type="email" name="email" placeholder="Email"><br><br>

    <input type="password" name="password" placeholder="Password" required><br><br>

    <select name="role">
        <option value="Manager">Manager</option>
        <option value="Technician">Technician</option>
    </select>

    <br><br>

    <button type="submit" name="register">Register</button>

</form>

</body>
</html>