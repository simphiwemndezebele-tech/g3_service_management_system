<?php

include("../config/db.php");

if(isset($_POST['save'])){

    $customer_name = $_POST['customer_name'];
    $company_name = $_POST['company_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];


    $sql = "INSERT INTO customers
    (customer_name, company_name, phone, email, address)

    VALUES
    ('$customer_name',
     '$company_name',
     '$phone',
     '$email',
     '$address')";


    if(mysqli_query($conn,$sql)){

        header("Location: view_customers.php");

    }else{

        echo "Error: ".mysqli_error($conn);

    }

}

?>