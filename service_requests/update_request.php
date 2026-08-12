<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

include("../config/db.php");


// ==============================
// Receive Request ID
// ==============================

$id = intval($_POST['id']);


// ==============================
// Technician Security Check
// ==============================

if ($_SESSION['role'] === 'Technician') {

    $user_id = $_SESSION['user_id'];

    $check_sql = "
        SELECT sr.id, sr.technician_id

        FROM service_requests sr

        INNER JOIN technicians t
        ON sr.technician_id = t.id

        WHERE sr.id = ?
        AND t.user_id = ?
    ";

    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $id,
        $user_id
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) === 0) {

        // Technician is trying to edit another technician's request
        header("Location: view_requests.php");
        exit();

    }

}


// ==============================
// Receive Form Data
// ==============================

$customer_id = intval($_POST['customer_id']);
$machine_id = intval($_POST['machine_id']);

$issue_description = $_POST['issue_description'];
$priority = $_POST['priority'];
$status = $_POST['status'];


// ==============================
// Technician Restriction
// ==============================

if ($_SESSION['role'] === 'Technician') {

    /*
     * Never trust technician_id
     * coming from the browser.
     *
     * Get the technician ID directly
     * from the logged-in user's account.
     */

    $user_id = $_SESSION['user_id'];

    $tech_sql = "
        SELECT id
        FROM technicians
        WHERE user_id = ?
    ";

    $tech_stmt = mysqli_prepare($conn, $tech_sql);

    mysqli_stmt_bind_param(
        $tech_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($tech_stmt);

    $tech_result = mysqli_stmt_get_result($tech_stmt);

    if ($tech = mysqli_fetch_assoc($tech_result)) {

        $technician_id = $tech['id'];

    } else {

        die("Technician account is not properly linked.");

    }

} else {

    // Manager and Reception can choose the technician
    $technician_id = intval($_POST['technician_id']);

}


// ==============================
// Update Service Request
// ==============================

$sql = "
    UPDATE service_requests

    SET
        customer_id = ?,
        machine_id = ?,
        technician_id = ?,
        issue_description = ?,
        priority = ?,
        status = ?

    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iiisssi",
    $customer_id,
    $machine_id,
    $technician_id,
    $issue_description,
    $priority,
    $status,
    $id
);


// ==============================
// Execute Update
// ==============================

if (mysqli_stmt_execute($stmt)) {

    header("Location: view_requests.php");
    exit();

} else {

    echo "Error updating service request: "
         . mysqli_error($conn);

}

?>