<?php

session_start();

include("../config/db.php");

$id=$_GET['id'];

/* Get Job Card */

$sql="SELECT * FROM job_cards WHERE id='$id'";

$result=mysqli_query($conn,$sql);

$row=mysqli_fetch_assoc($result);

$request_id=$row['service_request_id'];

/* Complete Job Card */

mysqli_query($conn,"
UPDATE job_cards
SET status='Completed'
WHERE id='$id'
");

/* Complete Service Request */

mysqli_query($conn,"
UPDATE service_requests
SET status='Completed'
WHERE id='$request_id'
");

header("Location:view_job_cards.php");

?>