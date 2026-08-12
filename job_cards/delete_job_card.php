<?php

session_start();

require_once("../includes/permissions.php");

/* ==============================
   Manager Only
   ============================== */

requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Get Job Card ID
   ============================== */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


/* ==============================
   Get Linked Service Request
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT service_request_id
     FROM job_cards
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    header("Location: view_job_cards.php");
    exit();

}

$row = mysqli_fetch_assoc($result);

$request_id = intval($row['service_request_id']);


/* ==============================
   Delete Job Card
   ============================== */

$delete_stmt = mysqli_prepare(
    $conn,
    "DELETE FROM job_cards
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $delete_stmt,
    "i",
    $id
);

if (mysqli_stmt_execute($delete_stmt)) {


    /* ==============================
       Return Service Request to Pending
       ============================== */

    $request_stmt = mysqli_prepare(
        $conn,
        "UPDATE service_requests
         SET status = 'Pending'
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $request_stmt,
        "i",
        $request_id
    );

    mysqli_stmt_execute($request_stmt);


    header("Location: view_job_cards.php");
    exit();


} else {

    echo "Error deleting Job Card: " . mysqli_error($conn);

}

?>