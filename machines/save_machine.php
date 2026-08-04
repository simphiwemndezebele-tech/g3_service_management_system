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

// Generate next Asset Number

$query = mysqli_query($conn, "
    SELECT asset_number
    FROM machines
    ORDER BY asset_number DESC
    LIMIT 1
");

if(mysqli_num_rows($query) > 0){

    $row = mysqli_fetch_assoc($query);

    // Example: G3-015 -> 15
    $last_number = (int) substr($row['asset_number'], 3);

    $next_number = $last_number + 1;

}else{

    $next_number = 1;

}

$asset_number = "G3-" . str_pad($next_number, 3, "0", STR_PAD_LEFT);

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