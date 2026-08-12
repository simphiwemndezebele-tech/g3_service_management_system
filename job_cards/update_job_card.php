<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Receive Form Data
   ============================== */

$id = intval($_POST['id'] ?? 0);

$work_done = trim($_POST['work_done'] ?? '');
$remarks   = trim($_POST['remarks'] ?? '');
$status    = $_POST['status'] ?? '';


if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


/* ==============================
   Validate Status
   ============================== */

$allowed_statuses = [
    'Open',
    'In Progress',
    'Completed'
];

if (!in_array($status, $allowed_statuses, true)) {
    die("Invalid Job Card status.");
}


/* ==============================
   Technician Security
   ============================== */

if ($_SESSION['role'] === 'Technician') {

    $user_id = intval($_SESSION['user_id']);

    $security_stmt = mysqli_prepare(
        $conn,
        "SELECT job_cards.id
         FROM job_cards
         INNER JOIN technicians
             ON job_cards.technician_id = technicians.id
         WHERE job_cards.id = ?
         AND technicians.user_id = ?"
    );

    mysqli_stmt_bind_param(
        $security_stmt,
        "ii",
        $id,
        $user_id
    );

    mysqli_stmt_execute($security_stmt);

    $security_result = mysqli_stmt_get_result($security_stmt);

    if (mysqli_num_rows($security_result) === 0) {

        header("Location: view_job_cards.php");
        exit();

    }
}


/* ==============================
   Update Job Card
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "UPDATE job_cards
     SET
        work_done = ?,
        remarks = ?,
        status = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "sssi",
    $work_done,
    $remarks,
    $status,
    $id
);


if (mysqli_stmt_execute($stmt)) {


    /* ==============================
       Get Linked Service Request
       ============================== */

    $job_stmt = mysqli_prepare(
        $conn,
        "SELECT service_request_id
         FROM job_cards
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $job_stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($job_stmt);

    $job_result = mysqli_stmt_get_result($job_stmt);

    $job = mysqli_fetch_assoc($job_result);

    $request_id = intval($job['service_request_id']);


    /* ==============================
       Keep Service Request in Sync
       ============================== */

    if ($status === "Completed") {

        $request_status = "Completed";

    } elseif ($status === "In Progress") {

        $request_status = "In Progress";

    } else {

        $request_status = "Pending";

    }


    $request_stmt = mysqli_prepare(
        $conn,
        "UPDATE service_requests
         SET status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $request_stmt,
        "si",
        $request_status,
        $request_id
    );

    mysqli_stmt_execute($request_stmt);


    /* ==============================
       Return to Job Cards
       ============================== */

    header("Location: view_job_cards.php");
    exit();


} else {

    echo "Error: " . mysqli_error($conn);

}

?>