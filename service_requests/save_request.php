<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

include("../config/db.php");

// Receive Form Data
$customer_id = $_POST['customer_id'];
$machine_id = $_POST['machine_id'];
$technician_id = $_POST['technician_id'];


// Technician security
if ($_SESSION['role'] === 'Technician') {

    $user_id = $_SESSION['user_id'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM technicians
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $tech_result = mysqli_stmt_get_result($stmt);

    if ($tech = mysqli_fetch_assoc($tech_result)) {

        // Force the request to belong to the logged-in technician
        $technician_id = $tech['id'];

    } else {

        die("Your technician account is not linked to a technician record.");

    }
}
$issue_description = $_POST['issue_description'];
$priority = $_POST['priority'];
$status = $_POST['status'];

// ==============================
// Generate Service Request Number
// Format: SR-2026-0001
// ==============================

$year = date("Y");

$query = mysqli_query($conn, "
    SELECT request_number
    FROM service_requests
    WHERE request_number LIKE 'SR-$year-%'
    ORDER BY request_number DESC
    LIMIT 1
");

if(mysqli_num_rows($query) > 0){

    $row = mysqli_fetch_assoc($query);

    $last_number = (int) substr($row['request_number'], -4);

    $next_number = $last_number + 1;

}else{

    $next_number = 1;

}

$request_number = "SR-" . $year . "-" . str_pad($next_number,4,"0",STR_PAD_LEFT);

// ==============================
// Save Request
// ==============================

$sql = "INSERT INTO service_requests
(
request_number,
customer_id,
machine_id,
technician_id,
issue_description,
priority,
status
)
VALUES
(?,?,?,?,?,?,?)";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"siiisss",
$request_number,
$customer_id,
$machine_id,
$technician_id,
$issue_description,
$priority,
$status
);

if(mysqli_stmt_execute($stmt)){

    header("Location:view_requests.php");
    exit();

}else{

    echo "Error: " . mysqli_error($conn);

}
?>