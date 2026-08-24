<?php

/*
|--------------------------------------------------------------------------
| G3 Systems
| Overdue Service Request Notification System
|--------------------------------------------------------------------------
|
| This file checks for service requests that:
|
| - Are still Pending or In Progress
| - Are older than 3 days
|
| It then notifies:
|
| - Managers
| - Reception users
| - The assigned Technician
|
| Both in-system notifications and email notifications
| are sent.
|
| Duplicate overdue notifications are prevented.
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Required Files
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/notification_functions.php";
require_once __DIR__ . "/email_functions.php";


/*
|--------------------------------------------------------------------------
| Process Overdue Service Requests
|--------------------------------------------------------------------------
*/

function processOverdueServiceRequests($conn)
{

    /*
    |--------------------------------------------------------------------------
    | Find Overdue Requests
    |--------------------------------------------------------------------------
    |
    | Pending or In Progress requests older than 3 days.
    |
    */

    $sql = "
        SELECT

            sr.id,
            sr.request_number,
            sr.request_date,
            sr.status,
            sr.priority,
            sr.issue_description,

            c.customer_name,

            m.asset_number,
            m.machine_model,

            t.id AS technician_id,
            t.full_name AS technician_name,
            t.user_id AS technician_user_id,
            t.email AS technician_email

        FROM service_requests sr

        LEFT JOIN customers c
            ON sr.customer_id = c.id

        LEFT JOIN machines m
            ON sr.machine_id = m.id

        LEFT JOIN technicians t
            ON sr.technician_id = t.id

        WHERE

            sr.status IN ('Pending', 'In Progress')

            AND sr.request_date <
                DATE_SUB(NOW(), INTERVAL 3 DAY)

        ORDER BY sr.request_date ASC
    ";


    $result = mysqli_query($conn, $sql);


    if (!$result) {

        error_log(
            "Overdue request query failed: " .
            mysqli_error($conn)
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Process Each Overdue Request
    |--------------------------------------------------------------------------
    */

    while ($request = mysqli_fetch_assoc($result)) {


        $request_id =
            intval($request['id']);

        $request_number =
            $request['request_number'];

        $customer_name =
            $request['customer_name'] ?? 'Unknown Customer';

        $asset_number =
            $request['asset_number'] ?? 'Unknown Machine';

        $status =
            $request['status'];

        $priority =
            $request['priority'] ?? 'Normal';


        /*
        |--------------------------------------------------------------------------
        | Notification Information
        |--------------------------------------------------------------------------
        */

        $title =
            "Overdue Service Request";


        $message =
            "Service request " .
            $request_number .
            " has been " .
            strtolower($status) .
            " for more than 3 days.\n\n" .

            "Customer: " .
            $customer_name . "\n" .

            "Machine: " .
            $asset_number . "\n" .

            "Priority: " .
            $priority . "\n" .

            "Status: " .
            $status . "\n\n" .

            "Please review this service request and take the necessary action.";


        /*
        |--------------------------------------------------------------------------
        | Find Managers and Reception Users
        |--------------------------------------------------------------------------
        */

        $users_sql = "
            SELECT
                id,
                full_name,
                email,
                role

            FROM users

            WHERE role IN ('Manager', 'Reception')
        ";


        $users_result =
            mysqli_query(
                $conn,
                $users_sql
            );


        if ($users_result) {

            while (
                $user =
                mysqli_fetch_assoc(
                    $users_result
                )
            ) {


                $user_id =
                    intval($user['id']);


                /*
                |--------------------------------------------------------------------------
                | Check Whether Overdue Notification Already Exists
                |--------------------------------------------------------------------------
                |
                | This prevents duplicate notifications every time
                | the dashboard is opened/refreshed.
                |
                */

                $check_stmt =
                    mysqli_prepare(
                        $conn,
                        "SELECT id
                         FROM notifications
                         WHERE user_id = ?
                         AND reference_id = ?
                         AND title = 'Overdue Service Request'
                         LIMIT 1"
                    );


                mysqli_stmt_bind_param(
                    $check_stmt,
                    "ii",
                    $user_id,
                    $request_id
                );


                mysqli_stmt_execute(
                    $check_stmt
                );


                $check_result =
                    mysqli_stmt_get_result(
                        $check_stmt
                    );


                /*
                |--------------------------------------------------------------------------
                | Only Send If Notification Does Not Exist
                |--------------------------------------------------------------------------
                */

                if (
                    mysqli_num_rows(
                        $check_result
                    ) === 0
                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | Create In-System Notification
                    |--------------------------------------------------------------------------
                    */

                    createNotification(
                        $conn,
                        $user_id,
                        $title,
                        $message,
                        "warning",
                        $request_id
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Send Email
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !empty(
                            $user['email']
                        )
                    ) {

                        sendEmail(
                            $user['email'],
                            $user['full_name'],
                            $title .
                            " - " .
                            $request_number,
                            $message
                        );

                    }

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Notify Assigned Technician
        |--------------------------------------------------------------------------
        */

        $technician_user_id =
            intval(
                $request['technician_user_id'] ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | Only Notify Technician If Properly Linked
        |--------------------------------------------------------------------------
        */

        if (
            $technician_user_id > 0
        ) {


            /*
            |--------------------------------------------------------------------------
            | Check Existing Technician Overdue Notification
            |--------------------------------------------------------------------------
            */

            $tech_check_stmt =
                mysqli_prepare(
                    $conn,
                    "SELECT id
                     FROM notifications
                     WHERE user_id = ?
                     AND reference_id = ?
                     AND title = 'Overdue Service Request'
                     LIMIT 1"
                );


            mysqli_stmt_bind_param(
                $tech_check_stmt,
                "ii",
                $technician_user_id,
                $request_id
            );


            mysqli_stmt_execute(
                $tech_check_stmt
            );


            $tech_check_result =
                mysqli_stmt_get_result(
                    $tech_check_stmt
                );


            /*
            |--------------------------------------------------------------------------
            | Create Technician Notification
            |--------------------------------------------------------------------------
            */

            if (
                mysqli_num_rows(
                    $tech_check_result
                ) === 0
            ) {


                /*
                |--------------------------------------------------------------------------
                | In-System Notification
                |--------------------------------------------------------------------------
                */

                createNotification(
                    $conn,
                    $technician_user_id,
                    $title,
                    $message,
                    "warning",
                    $request_id
                );


                /*
                |--------------------------------------------------------------------------
                | Technician Email
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $request['technician_email']
                    )
                ) {

                    sendEmail(
                        $request['technician_email'],
                        $request['technician_name'],
                        $title .
                        " - " .
                        $request_number,
                        $message
                    );

                }

            }

        }

    }

}

?>