<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Technician']);

if(!isset($_SESSION['username'])){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

$id = intval($_GET['id']);

/* Get linked Service Request */

$result = mysqli_query($conn,"
SELECT service_request_id
FROM job_cards
WHERE id='$id'
");

if(mysqli_num_rows($result)==0){

    header("Location:view_job_cards.php");
    exit();

}

$row = mysqli_fetch_assoc($result);

$request_id = $row['service_request_id'];

/* Delete Job Card */

mysqli_query($conn,"
DELETE FROM job_cards
WHERE id='$id'
");

/* Return Service Request to Pending */

mysqli_query($conn,"
UPDATE service_requests
SET status='Pending'
WHERE id='$request_id'
");

header("Location:view_job_cards.php");
exit();
?>