<?php

session_start();

require_once("../includes/permissions.php");

requireRole(['Manager', 'Reception', 'Technician']);

include("../config/db.php");

require_once("../includes/notification_functions.php");
require_once("../includes/email_functions.php");


// ==============================
// Receive Request ID
// ==============================

$id = intval($_POST['id']);


// ==============================
// Get Current Request Information
// BEFORE Updating
// ==============================

$old_sql = "
    SELECT
        sr.request_number,
        sr.status,
        sr.technician_id,
        t.user_id AS technician_user_id
    FROM service_requests sr

    LEFT JOIN technicians t
    ON sr.technician_id = t.id

    WHERE sr.id = ?
";

$old_stmt = mysqli_prepare($conn, $old_sql);

mysqli_stmt_bind_param(
    $old_stmt,
    "i",
    $id
);

mysqli_stmt_execute($old_stmt);

$old_result = mysqli_stmt_get_result($old_stmt);

$old_request = mysqli_fetch_assoc($old_result);

if (!$old_request) {

    die("Service request not found.");

}

$request_number = $old_request['request_number'];
$old_status = $old_request['status'];
$old_technician_id = $old_request['technician_id'];


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

    $check_stmt = mysqli_prepare(
        $conn,
        $check_sql
    );

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $id,
        $user_id
    );

    mysqli_stmt_execute($check_stmt);

    $check_result =
        mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) === 0) {

        header("Location: view_requests.php");
        exit();

    }

}


// ==============================
// Receive Form Data
// ==============================

$customer_id =
    intval($_POST['customer_id']);

$machine_id =
    intval($_POST['machine_id']);

$issue_description =
    trim($_POST['issue_description'] ?? '');

$priority =
    $_POST['priority'] ?? '';

$status =
    $_POST['status'] ?? '';


// ==============================
// Technician Restriction
// ==============================

if ($_SESSION['role'] === 'Technician') {

    $user_id = $_SESSION['user_id'];

    $tech_sql = "
        SELECT id
        FROM technicians
        WHERE user_id = ?
    ";

    $tech_stmt = mysqli_prepare(
        $conn,
        $tech_sql
    );

    mysqli_stmt_bind_param(
        $tech_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($tech_stmt);

    $tech_result =
        mysqli_stmt_get_result($tech_stmt);

    if ($tech = mysqli_fetch_assoc(
        $tech_result
    )) {

        $technician_id = $tech['id'];

    } else {

        die(
            "Technician account is not properly linked."
        );

    }

} else {

    // Manager and Reception can choose technician

    $technician_id =
        intval($_POST['technician_id']);

}


// ==============================
// Check Existing Job Card
// ==============================

$job_card_stmt = mysqli_prepare(
    $conn,
    "SELECT id, status
     FROM job_cards
     WHERE service_request_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $job_card_stmt,
    "i",
    $id
);

mysqli_stmt_execute($job_card_stmt);

$job_card_result = mysqli_stmt_get_result(
    $job_card_stmt
);


// ==============================
// Update Service Request
// ==============================

if (mysqli_num_rows($job_card_result) > 0) {

    /*
     * Job Card already exists.
     *
     * The Job Card controls the workflow status,
     * so we do NOT allow the Service Request status
     * to be changed independently.
     */

    $sql = "
        UPDATE service_requests

        SET
            customer_id = ?,
            machine_id = ?,
            technician_id = ?,
            issue_description = ?,
            priority = ?

        WHERE id = ?
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iisssi",
        $customer_id,
        $machine_id,
        $technician_id,
        $issue_description,
        $priority,
        $id
    );

} else {

    /*
     * No Job Card exists yet.
     * Service Request status can still be updated.
     */

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

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

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

}

// ==============================
// Execute Update
// ==============================

if (mysqli_stmt_execute($stmt)) {

    // ==================================================
    // Update Linked Service Request Status
    // ==================================================

    $request_stmt = mysqli_prepare(
        $conn,
        "UPDATE service_requests
         SET status = ?
         WHERE id = (
             SELECT service_request_id
             FROM job_cards
             WHERE id = ?
         )"
    );

    $request_status = ($status === 'Open') ? 'Pending' : $status;

    mysqli_stmt_bind_param(
      $request_stmt,
      "si",
      $request_status,
      $id
);
    mysqli_stmt_execute($request_stmt);
    
   // ==================================================
   // Redirect After Successful Update
   // ==================================================

        header("Location: view_job_cards.php");
        exit();
}

    // ==================================================
    // 1. STATUS CHANGE NOTIFICATIONS
    // ==================================================

    if ($old_status !== $status) {


        // ==================================================
        // STATUS = IN PROGRESS
        // STATUS = COMPLETED
        // ==================================================

        if (
            $status === 'In Progress' ||
            $status === 'Completed'
        ) {


            // ==================================================
            // Prepare Message
            // ==================================================

            if ($status === 'In Progress') {

                $manager_title =
                    "Service Request Started";

                $manager_message =
                    "Service request " .
                    $request_number .
                    " is now In Progress.";

                $reception_title =
                    "Service Request In Progress";

                $reception_message =
                    "Service request " .
                    $request_number .
                    " is now being worked on.";

            } else {

                $manager_title =
                    "Service Request Completed";

                $manager_message =
                    "Service request " .
                    $request_number .
                    " has been completed.";

                $reception_title =
                    "Service Request Completed";

                $reception_message =
                    "Service request " .
                    $request_number .
                    " has been completed.";

            }


            // ==================================================
            // EMAIL SUBJECT
            // ==================================================

            $email_subject =
                "Service Request " .
                $request_number .
                " - " .
                $status;


            // ==================================================
            // MANAGER NOTIFICATIONS + EMAIL
            // ==================================================

            $manager_result = mysqli_query(
                $conn,
                "
                SELECT
                    id,
                    full_name,
                    email
                FROM users
                WHERE role = 'Manager'
                "
            );

            while (
                $manager =
                mysqli_fetch_assoc(
                    $manager_result
                )
            ) {


                // ------------------------------------------
                // Don't notify the person who made the update
                // ------------------------------------------

                if (
                    $manager['id']
                    ==
                    $_SESSION['user_id']
                ) {

                    continue;

                }


                // ------------------------------------------
                // In-System Notification
                // ------------------------------------------

                createNotification(
                    $conn,
                    $manager['id'],
                    $manager_title,
                    $manager_message,
                    "info",
                    $id
                );


                // ------------------------------------------
                // Email Notification
                // ------------------------------------------

                if (
                    !empty($manager['email'])
                ) {

                    $manager_email_message = "

Hello " .
htmlspecialchars(
    $manager['full_name']
) . ",

Service request " .
$request_number .
" has been updated.

Current Status: " .
$status . "

Priority: " .
$priority . "

Issue Description:

" .
$issue_description . "

Please log in to the G3 Systems
Service Management System to view
the full service request.

Regards,
G3 Systems
Service Management System
";


                    sendEmail(
                        $manager['email'],
                        $manager['full_name'],
                        $email_subject,
                        $manager_email_message
                    );

                }

            }


            // ==================================================
            // RECEPTION NOTIFICATIONS + EMAIL
            // ==================================================

            $reception_result = mysqli_query(
                $conn,
                "
                SELECT
                    id,
                    full_name,
                    email
                FROM users
                WHERE role = 'Reception'
                "
            );

            while (
                $reception =
                mysqli_fetch_assoc(
                    $reception_result
                )
            ) {


                // ------------------------------------------
                // Don't notify the person who made the update
                // ------------------------------------------

                if (
                    $reception['id']
                    ==
                    $_SESSION['user_id']
                ) {

                    continue;

                }


                // ------------------------------------------
                // In-System Notification
                // ------------------------------------------

                createNotification(
                    $conn,
                    $reception['id'],
                    $reception_title,
                    $reception_message,
                    "info",
                    $id
                );


                // ------------------------------------------
                // Email Notification
                // ------------------------------------------

                if (
                    !empty($reception['email'])
                ) {

                    $reception_email_message = "

Hello " .
htmlspecialchars(
    $reception['full_name']
) . ",

Service request " .
$request_number .
" has been updated.

Current Status: " .
$status . "

Priority: " .
$priority . "

Issue Description:

" .
$issue_description . "

Please log in to the G3 Systems
Service Management System to view
the full service request.

Regards,
G3 Systems
Service Management System
";


                    sendEmail(
                        $reception['email'],
                        $reception['full_name'],
                        $email_subject,
                        $reception_email_message
                    );

                }

            }

        }

    }


    // ==================================================
    // 2. TECHNICIAN ASSIGNMENT NOTIFICATION
    // ==================================================

    if (
        $technician_id != $old_technician_id
        &&
        !empty($technician_id)
    ) {


        // ------------------------------------------
        // Get New Technician Information
        // ------------------------------------------

        $tech_user_stmt = mysqli_prepare(
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
            $tech_user_stmt,
            "i",
            $technician_id
        );

        mysqli_stmt_execute(
            $tech_user_stmt
        );

        $tech_user_result =
            mysqli_stmt_get_result(
                $tech_user_stmt
            );


        if (
            $tech_user =
            mysqli_fetch_assoc(
                $tech_user_result
            )
        ) {

            $technician_user_id =
                $tech_user['user_id'];

            $technician_name =
                $tech_user['full_name'];

            $technician_email =
                $tech_user['email'];


            // ==================================================
            // In-System Technician Notification
            // ==================================================

            if (
                !empty($technician_user_id)
            ) {

                createNotification(
                    $conn,
                    $technician_user_id,
                    "Service Request Assigned",
                    "Service request " .
                    $request_number .
                    " has been assigned to you.",
                    "info",
                    $id
                );

            }


            // ==================================================
            // Technician Email
            // ==================================================

            if (
                !empty($technician_email)
            ) {

                $technician_email_subject =
                    "Service Request Assigned - " .
                    $request_number;


                $technician_email_message = "

Hello " .
htmlspecialchars(
    $technician_name
) . ",

Service request " .
$request_number .
" has been assigned to you.

Priority: " .
$priority . "

Current Status: " .
$status . "

Issue Description:

" .
$issue_description . "

Please log in to the G3 Systems
Service Management System to view
the full service request and take
the necessary action.

Regards,
G3 Systems
Service Management System
";


                sendEmail(
                    $technician_email,
                    $technician_name,
                    $technician_email_subject,
                    $technician_email_message
                );

            }

        }

    }


    // ==================================================
    // Return
    // ==================================================

    header(
        "Location: view_requests.php"
    );

    exit();


} else {

    echo
        "Error updating service request: "
        . mysqli_error($conn);

}

?>