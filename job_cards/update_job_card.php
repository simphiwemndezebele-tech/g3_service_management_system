<?php
session_start();

include("../config/db.php");

$id = $_POST['id'];

$work_done = mysqli_real_escape_string($conn, $_POST['work_done']);
$remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

/* Update Job Card */

$sql = "UPDATE job_cards SET

work_done='$work_done',
remarks='$remarks',
status='$status'

WHERE id='$id'";

if(mysqli_query($conn,$sql)){

    /* Get linked Service Request */

    $job = mysqli_query($conn,"
    SELECT service_request_id
    FROM job_cards
    WHERE id='$id'
    ");

    $row = mysqli_fetch_assoc($job);

    $request_id = $row['service_request_id'];

    /* Keep Service Request in sync */

    if($status=="Completed"){

        mysqli_query($conn,"
        UPDATE service_requests
        SET status='Completed'
        WHERE id='$request_id'
        ");

    }elseif($status=="In Progress"){

        mysqli_query($conn,"
        UPDATE service_requests
        SET status='In Progress'
        WHERE id='$request_id'
        ");

    }else{

        mysqli_query($conn,"
        UPDATE service_requests
        SET status='Pending'
        WHERE id='$request_id'
        ");

    }

    header("Location:view_job_cards.php");
    exit();

}else{

    echo "Error: ".mysqli_error($conn);

}
?>