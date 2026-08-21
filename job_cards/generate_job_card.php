<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

require_once("../includes/notification_functions.php");
require_once("../includes/email_functions.php");


// ==================================================
// Check Service Request ID
// ==================================================

if (!isset($_GET['id'])) {

    die("Invalid Service Request.");

}

$request_id = intval($_GET['id']);


// ==================================================
// Check if Job Card Already Exists
// ==================================================

$check_stmt = mysqli_prepare(
    $conn,
    "
    SELECT id
    FROM job_cards
    WHERE service_request_id = ?
    "
);

mysqli_stmt_bind_param(
    $check_stmt,
    "i",
    $request_id
);

mysqli_stmt_execute($check_stmt);

$check_result =
    mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {

    echo "
    <script>
        alert('Job Card already generated!');
        window.location='../service_requests/view_requests.php';
    </script>
    ";

    exit();

}


// ==================================================
// Get Service Request
// ==================================================

$request_stmt = mysqli_prepare(
    $conn,
    "
    SELECT *
    FROM service_requests
    WHERE id = ?
    "
);

mysqli_stmt_bind_param(
    $request_stmt,
    "i",
    $request_id
);

mysqli_stmt_execute($request_stmt);

$request_result =
    mysqli_stmt_get_result($request_stmt);


if (mysqli_num_rows($request_result) == 0) {

    die("Service Request not found.");

}

$row = mysqli_fetch_assoc(
    $request_result
);


// ==================================================
// Generate Job Card Number
// ==================================================

$year = date("Y");

$result = mysqli_query(
    $conn,
    "
    SELECT MAX(id) AS last_id
    FROM job_cards
    "
);

$data = mysqli_fetch_assoc($result);

$next =
    ((int)$data['last_id']) + 1;

$job_number =
    "JC-" .
    $year .
    "-" .
    str_pad(
        $next,
        4,
        "0",
        STR_PAD_LEFT
    );


// ==================================================
// Match Job Card Status
// With Service Request Status
// ==================================================

if ($row['status'] == "Completed") {

    $job_status = "Completed";

} elseif ($row['status'] == "In Progress") {

    $job_status = "In Progress";

} else {

    $job_status = "Open";

}


// ==================================================
// Insert Job Card
// ==================================================

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

$stmt = mysqli_prepare(
    $conn,
    $sql
);

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


// ==================================================
// Execute Insert
// ==================================================

if (mysqli_stmt_execute($stmt)) {


    // ==================================================
    // JOB CARD CREATED SUCCESSFULLY
    // ==================================================


    // ==================================================
    // Find Assigned Technician
    // ==================================================

    $technician_id =
        intval($row['technician_id']);


    if (!empty($technician_id)) {


        $tech_stmt = mysqli_prepare(
            $conn,
            "
            SELECT
                t.user_id,
                t.full_name,
                t.email
            FROM technicians t
            WHERE t.id = ?
            "
        );

        mysqli_stmt_bind_param(
            $tech_stmt,
            "i",
            $technician_id
        );

        mysqli_stmt_execute(
            $tech_stmt
        );

        $tech_result =
            mysqli_stmt_get_result(
                $tech_stmt
            );


        if (
            $technician =
            mysqli_fetch_assoc(
                $tech_result
            )
        ) {


            $technician_user_id =
                $technician['user_id'];

            $technician_name =
                $technician['full_name'];

            $technician_email =
                $technician['email'];


            // ==================================================
            // In-System Notification
            // ==================================================

            if (
                !empty($technician_user_id)
            ) {

                createNotification(
                    $conn,
                    $technician_user_id,
                    "New Job Card",
                    "Job Card " .
                    $job_number .
                    " has been generated and assigned to you.",
                    "info",
                    mysqli_insert_id($conn)
                );

            }


            // ==================================================
            // Email Notification
            // ==================================================

            if (
                !empty($technician_email)
            ) {


                $email_subject =
                    "New Job Card Assigned - " .
                    $job_number;


                $email_message = "

Hello " .
htmlspecialchars(
    $technician_name
) . ",

A new Job Card has been generated
and assigned to you.

Job Card Number:
" .
$job_number . "

Service Request:
" .
$row['request_number'] . "

Status:
" .
$job_status . "

Priority:
" .
$row['priority'] . "

Issue Description:

" .
$row['issue_description'] . "

Please log in to the G3 Systems
Service Management System to view
the Job Card and take the necessary
action.

Regards,

G3 Systems
Service Management System

";


                sendEmail(
                    $technician_email,
                    $technician_name,
                    $email_subject,
                    $email_message
                );

            }

        }

    }


    // ==================================================
    // Return to Job Cards
    // ==================================================

    header(
        "Location: view_job_cards.php"
    );

    exit();


} else {

    echo
        "Error generating Job Card: " .
        mysqli_error($conn);

}

?>