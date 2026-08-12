<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

if (!isset($_GET['id'])) {
    die("Invalid Service Request.");
}

$request_id = intval($_GET['id']);

/* Check if Job Card already exists */
$check = mysqli_query($conn,
"SELECT id FROM job_cards WHERE service_request_id='$request_id'");

if(mysqli_num_rows($check) > 0){
    echo "<script>
    alert('Job Card already generated!');
    window.location='../service_requests/view_requests.php';
    </script>";
    exit();
}

/* Get Service Request */
$request = mysqli_query($conn,"
SELECT *
FROM service_requests
WHERE id='$request_id'
");

if(mysqli_num_rows($request)==0){
    die("Service Request not found.");
}

$row = mysqli_fetch_assoc($request);

/* Generate Job Card Number */
$year = date("Y");

$result = mysqli_query($conn,"SELECT MAX(id) AS last_id FROM job_cards");
$data = mysqli_fetch_assoc($result);

$next = $data['last_id'] + 1;

$job_number = "JC-".$year."-".str_pad($next,4,"0",STR_PAD_LEFT);

/* Insert Job Card */

mysqli_query($conn,"
INSERT INTO job_cards(

job_card_number,
service_request_id,
customer_id,
machine_id,
technician_id,
issue_description

)

VALUES(

'$job_number',
'{$row['id']}',
'{$row['customer_id']}',
'{$row['machine_id']}',
'{$row['technician_id']}',
'{$row['issue_description']}'

)

");

/* Update Request Status */

mysqli_query($conn,"
UPDATE service_requests

SET status='In Progress'

WHERE id='$request_id'
");

header("Location:view_job_cards.php");

exit();
?>