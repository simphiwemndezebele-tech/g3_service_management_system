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
$check = mysqli_query(
    $conn,
    "SELECT id FROM job_cards WHERE service_request_id='$request_id'"
);

if (mysqli_num_rows($check) > 0) {
    echo "<script>
        alert('Job Card already generated!');
        window.location='../service_requests/view_requests.php';
    </script>";
    exit();
}

/* Get Service Request */
$request = mysqli_query(
    $conn,
    "SELECT *
     FROM service_requests
     WHERE id='$request_id'"
);

if (mysqli_num_rows($request) == 0) {
    die("Service Request not found.");
}

$row = mysqli_fetch_assoc($request);

/* Generate Job Card Number */
$year = date("Y");

$result = mysqli_query(
    $conn,
    "SELECT MAX(id) AS last_id FROM job_cards"
);

$data = mysqli_fetch_assoc($result);

$next = ((int)$data['last_id']) + 1;

$job_number = "JC-" . $year . "-" . str_pad($next, 4, "0", STR_PAD_LEFT);

/*
 * Match Job Card status with Service Request status
 */
if ($row['status'] == "Completed") {

    $job_status = "Completed";

} elseif ($row['status'] == "In Progress") {

    $job_status = "In Progress";

} else {

    $job_status = "Open";
}

/* Insert Job Card */
$sql = "
INSERT INTO job_cards
(
    job_card_number,
    service_request_id,
    customer_id,
    machine_id,
    technician_id,
    issue_description,
    status
)
VALUES
(
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?
)
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "siiiiss",
    $job_number,
    $row['id'],
    $row['customer_id'],
    $row['machine_id'],
    $row['technician_id'],
    $row['issue_description'],
    $job_status
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: view_job_cards.php");
    exit();

} else {

    echo "Error generating Job Card: " . mysqli_error($conn);
}
?>