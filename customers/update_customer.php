<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);
include("../config/db.php");

if(isset($_POST['update'])){

    $id = $_POST['id'];
    $customer_name = $_POST['customer_name'];
    $company_name = $_POST['company_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $sql = "UPDATE customers SET

    customer_name='$customer_name',
    company_name='$company_name',
    phone='$phone',
    email='$email',
    address='$address'

    WHERE id='$id'";

    if(mysqli_query($conn,$sql)){

        header("Location: view_customers.php");
        exit();

    }else{

        echo mysqli_error($conn);

    }

}

?>