<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

include("../config/db.php");
require_once("../includes/notification_functions.php");
require_once("../includes/email_functions.php");


// ======================================================
// RECEIVE FORM DATA
// ======================================================

$customer_id = intval($_POST['customer_id'] ?? 0);
$machine_id = intval($_POST['machine_id'] ?? 0);
$technician_id = intval($_POST['technician_id'] ?? 0);

$issue_description = trim($_POST['issue_description'] ?? '');
$priority = $_POST['priority'] ?? '';
$status = $_POST['status'] ?? '';


// ======================================================
// TECHNICIAN SECURITY
// ======================================================

if ($_SESSION['role'] === 'Technician') {

    $user_id = $_SESSION['user_id'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM technicians
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $tech_result = mysqli_stmt_get_result($stmt);

    if ($tech = mysqli_fetch_assoc($tech_result)) {

        // Force the request to belong to the logged-in technician
        $technician_id = $tech['id'];

    } else {

        die("Your technician account is not linked to a technician record.");

    }
}


// ======================================================
// GENERATE SERVICE REQUEST NUMBER
// Format: SR-2026-0001
// ======================================================

$year = date("Y");

$query = mysqli_query(
    $conn,
    "
    SELECT request_number
    FROM service_requests
    WHERE request_number LIKE 'SR-$year-%'
    ORDER BY request_number DESC
    LIMIT 1
    "
);

if (mysqli_num_rows($query) > 0) {

    $row = mysqli_fetch_assoc($query);

    $last_number = (int) substr(
        $row['request_number'],
        -4
    );

    $next_number = $last_number + 1;

} else {

    $next_number = 1;

}

$request_number =
    "SR-" .
    $year .
    "-" .
    str_pad(
        $next_number,
        4,
        "0",
        STR_PAD_LEFT
    );


// ======================================================
// SAVE SERVICE REQUEST
// ======================================================

$sql = "
    INSERT INTO service_requests
    (
        request_number,
        customer_id,
        machine_id,
        technician_id,
        issue_description,
        priority,
        status
    )
    VALUES
    (?,?,?,?,?,?,?)
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "siiisss",
    $request_number,
    $customer_id,
    $machine_id,
    $technician_id,
    $issue_description,
    $priority,
    $status
);


// ======================================================
// EXECUTE
// ======================================================

if (mysqli_stmt_execute($stmt)) {

    // Get the newly created service request ID
    $service_request_id = mysqli_insert_id($conn);


    // ==================================================
    // 1. TECHNICIAN NOTIFICATION + EMAIL
    // ==================================================

    $tech_user_stmt = mysqli_prepare(
        $conn,
        "SELECT
            user_id,
            full_name,
            email
         FROM technicians
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $tech_user_stmt,
        "i",
        $technician_id
    );

    mysqli_stmt_execute($tech_user_stmt);

    $tech_user_result =
        mysqli_stmt_get_result($tech_user_stmt);


    if ($technician = mysqli_fetch_assoc($tech_user_result)) {

        $technician_user_id =
            $technician['user_id'];

        $technician_name =
            $technician['full_name'];

        $technician_email =
            $technician['email'];


        // ----------------------------------------------
        // In-System Notification
        // ----------------------------------------------

        if (!empty($technician_user_id)) {

            createNotification(
                $conn,
                $technician_user_id,
                "New Service Request",
                "Service request " .
                $request_number .
                " has been assigned to you.",
                "info",
                $service_request_id
            );
        }


        // ----------------------------------------------
        // Technician Email
        // ----------------------------------------------

        if (!empty($technician_email)) {

            $email_subject =
                "New Service Request - " .
                $request_number;

            $email_message = "
Hello " . $technician_name . ",

A new service request has been assigned to you.

Service Request: " . $request_number . "

Priority: " . $priority . "

Status: " . $status . "

Issue Description:
" . $issue_description . "

Please log in to the G3 Systems Service Management System
to view the full service request and take the necessary action.

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


    // ==================================================
    // 2. CUSTOMER EMAIL
    // ==================================================

    /*
     * Get the customer's name and email
     * from the existing customers table.
     */

    $customer_stmt = mysqli_prepare(
        $conn,
        "SELECT
            customer_name,
            email
         FROM customers
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $customer_stmt,
        "i",
        $customer_id
    );

    mysqli_stmt_execute($customer_stmt);

    $customer_result =
        mysqli_stmt_get_result($customer_stmt);


    if ($customer = mysqli_fetch_assoc($customer_result)) {

        $customer_name =
            $customer['customer_name'];

        $customer_email =
            $customer['email'];


        // ----------------------------------------------
        // Only send if customer has an email
        // ----------------------------------------------

        if (!empty($customer_email)) {

            $customer_subject =
                "Service Request Received - " .
                $request_number;


            $customer_message = "
Hello " . $customer_name . ",

Thank you for contacting G3 Systems.

Your service request has been successfully received and registered in our Service Management System.

Service Request: " . $request_number . "

Priority: " . $priority . "

Current Status: " . $status . "

Issue Description:
" . $issue_description . "

Our service team will review your request and take the necessary action.

Please keep your Service Request number for future reference:

" . $request_number . "

Regards,
G3 Systems
Service Management System
";


            sendEmail(
                $customer_email,
                $customer_name,
                $customer_subject,
                $customer_message
            );
        }
    }


    // ==================================================
    // RETURN TO SERVICE REQUESTS
    // ==================================================

    header("Location: view_requests.php");
    exit();


} else {

    echo "Error: " . mysqli_error($conn);

}

?>