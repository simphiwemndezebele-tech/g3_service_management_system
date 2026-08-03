<?php

include("../config/db.php");

if(isset($_POST['save'])){

    $customer_id = $_POST['customer_id'];
    $asset_number = $_POST['asset_number'];
    $brand = $_POST['brand'];
    $machine_model = $_POST['machine_model'];
    $machine_type = $_POST['machine_type'];
    $serial_number = $_POST['serial_number'];
    $ip_address = $_POST['ip_address'];
    $location = $_POST['location'];
    $installation_date = $_POST['installation_date'];
    $status = $_POST['status'];

    $sql = "INSERT INTO machines
    (customer_id, asset_number, brand, machine_model, machine_type,
     serial_number, ip_address, location, installation_date, status)

    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "isssssssss",
        $customer_id,
        $asset_number,
        $brand,
        $machine_model,
        $machine_type,
        $serial_number,
        $ip_address,
        $location,
        $installation_date,
        $status
    );

    if(mysqli_stmt_execute($stmt)){

        header("Location: view_machines.php");
        exit();

    }else{

        echo "Error: " . mysqli_error($conn);

    }

}
?>