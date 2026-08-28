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
// Receive Form Data
// ==================================================

$id = intval($_POST['id'] ?? 0);

$work_done = trim($_POST['work_done'] ?? '');
$remarks   = trim($_POST['remarks'] ?? '');
$status    = $_POST['status'] ?? '';


if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


// ==================================================
// Validate Status
// ==================================================

$allowed_statuses = [
    'Open',
    'In Progress',
    'Completed'
];

if (!in_array($status, $allowed_statuses, true)) {
    die("Invalid Job Card status.");
}


// ==================================================
// Get Existing Job Card Information
// BEFORE Updating
// ==================================================

$old_stmt = mysqli_prepare(
    $conn,
    "SELECT
        jc.job_card_number,
        jc.status,
        jc.technician_id,
        jc.service_request_id,
        jc.customer_id,
        jc.machine_id,
        jc.work_done,
        jc.remarks,
        sr.request_number,
        c.customer_name,
        c.email AS customer_email,
        m.asset_number,
        m.machine_model
     FROM job_cards jc

     LEFT JOIN service_requests sr
        ON jc.service_request_id = sr.id

     LEFT JOIN customers c
        ON jc.customer_id = c.id

     LEFT JOIN machines m
        ON jc.machine_id = m.id

     WHERE jc.id = ?"
);

mysqli_stmt_bind_param(
    $old_stmt,
    "i",
    $id
);

mysqli_stmt_execute($old_stmt);

$old_result = mysqli_stmt_get_result($old_stmt);

$old_job = mysqli_fetch_assoc($old_result);


if (!$old_job) {
    die("Job Card not found.");
}


$old_status =
    $old_job['status'];

$job_card_number =
    $old_job['job_card_number'];

$technician_id =
    intval($old_job['technician_id']);

$request_id =
    intval($old_job['service_request_id']);


// ==================================================
// Technician Security
// ==================================================

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

    $security_result =
        mysqli_stmt_get_result(
            $security_stmt
        );

    if (
        mysqli_num_rows(
            $security_result
        ) === 0
    ) {

        header(
            "Location: view_job_cards.php"
        );

        exit();
    }
}


// ==================================================
// Technician Status Security
// ==================================================

if ($_SESSION['role'] === 'Technician') {

    /*
     * Technicians may move jobs forward only:
     *
     * Open → In Progress
     * In Progress → Completed
     *
     * Completed → anything else is NOT allowed.
     */

    if ($old_status === 'Completed' && $status !== 'Completed') {

        echo "
        <script>
            alert('A completed Job Card cannot be reopened.');
            window.location='edit_job_card.php?id=$id';
        </script>
        ";

        exit();
    }


    /*
     * Prevent technicians from skipping
     * directly from Open → Completed.
     */

    if ($old_status === 'Open' && $status === 'Completed') {

        echo "
        <script>
            alert('A Job Card must be In Progress before it can be Completed.');
            window.location='edit_job_card.php?id=$id';
        </script>
        ";

        exit();
    }

}


// ==================================================
// Update Job Card
// ==================================================

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


    // ==================================================
    // Keep Service Request In Sync
    // ==================================================

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

    mysqli_stmt_execute(
        $request_stmt
    );


    // ==================================================
    // STATUS CHANGE NOTIFICATIONS
    // ==================================================

    if ($old_status !== $status) {


        // ==================================================
        // Find Manager Users
        // ==================================================

        $manager_result = mysqli_query(
            $conn,
            "SELECT
                id,
                full_name,
                email
             FROM users
             WHERE role = 'Manager'"
        );


        while (
            $manager =
            mysqli_fetch_assoc(
                $manager_result
            )
        ) {

            if ($status === "In Progress") {

                $title =
                    "Job Card In Progress";

                $message =
                    "Job Card " .
                    $job_card_number .
                    " is now In Progress.";

            } elseif ($status === "Completed") {

                $title =
                    "Job Card Completed";

                $message =
                    "Job Card " .
                    $job_card_number .
                    " has been completed.";

            } else {

                $title =
                    "Job Card Status Updated";

                $message =
                    "Job Card " .
                    $job_card_number .
                    " status has changed to Open.";
            }


            // In-System Notification

            createNotification(
                $conn,
                $manager['id'],
                $title,
                $message,
                "info",
                $id
            );


            // Manager Email

            if (!empty($manager['email'])) {

                sendEmail(
                    $manager['email'],
                    $manager['full_name'],
                    $title,
                    $message
                );
            }
        }


        // ==================================================
        // Find Reception Users
        // ==================================================

        $reception_result = mysqli_query(
            $conn,
            "SELECT
                id,
                full_name,
                email
             FROM users
             WHERE role = 'Reception'"
        );


        while (
            $reception =
            mysqli_fetch_assoc(
                $reception_result
            )
        ) {

            if ($status === "In Progress") {

                $title =
                    "Job Card In Progress";

                $message =
                    "Job Card " .
                    $job_card_number .
                    " is now In Progress.";

            } elseif ($status === "Completed") {

                $title =
                    "Job Card Completed";

                $message =
                    "Job Card " .
                    $job_card_number .
                    " has been completed.";

            } else {

                $title =
                    "Job Card Status Updated";

                $message =
                    "Job Card " .
                    $job_card_number .
                    " status has changed to Open.";
            }


            // In-System Notification

            createNotification(
                $conn,
                $reception['id'],
                $title,
                $message,
                "info",
                $id
            );


            // Reception Email

            if (!empty($reception['email'])) {

                sendEmail(
                    $reception['email'],
                    $reception['full_name'],
                    $title,
                    $message
                );
            }
        }


        // ==================================================
        // CUSTOMER COMPLETION EMAIL
        // ==================================================

        /*
         * Only send the customer email when the Job Card
         * actually changes to Completed.
         *
         * This prevents duplicate emails if somebody edits
         * the completed Job Card later.
         */

        if (
            $status === "Completed"
            &&
            $old_status !== "Completed"
            &&
            !empty($old_job['customer_email'])
        ) {


            $customer_name =
                $old_job['customer_name'];

            $customer_email =
                $old_job['customer_email'];

            $request_number =
                $old_job['request_number'];

            $asset_number =
                $old_job['asset_number'];

            $machine_model =
                $old_job['machine_model'];


            // ----------------------------------------------
            // Customer Email Subject
            // ----------------------------------------------

            $customer_subject =
                "Service Completed - " .
                $request_number;


            // ----------------------------------------------
            // Customer Email Message
            // ----------------------------------------------

            $customer_message = "
Hello " . $customer_name . ",

We are pleased to inform you that your service request has been completed by G3 Systems.

Service Request: " . $request_number . "

Job Card: " . $job_card_number . "

Machine Asset Number: " . $asset_number . "

Machine Model: " . $machine_model . "

Status: Completed

Work Completed:
" . $work_done . "

Remarks:
" . $remarks . "

Your machine service has now been completed.

If you have any questions regarding this service, please contact G3 Systems.

Thank you for choosing G3 Systems.

Regards,
G3 Systems
Service Management System
";


            // ----------------------------------------------
            // Send Customer Email
            // ----------------------------------------------

            sendEmail(
                $customer_email,
                $customer_name,
                $customer_subject,
                $customer_message
            );
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
        "Error updating Job Card: " .
        mysqli_error($conn);
}

?>