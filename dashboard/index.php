<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
require_once("../includes/notification_functions.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

require_once("../includes/overdue_notifications.php");


// ==================================================
// CURRENT USER
// ==================================================

$role = $_SESSION['role'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);

// ==============================
// Unread Notifications
// ==============================

$unread_notifications = 0;

if (isset($_SESSION['user_id'])) {

    $unread_notifications =
        getUnreadNotificationCount(
            $conn,
            (int)$_SESSION['user_id']
        );

}


// ==================================================
// PROCESS OVERDUE SERVICE REQUESTS
// ==================================================

processOverdueServiceRequests($conn);


// ==================================================
// FIND TECHNICIAN ID FOR LOGGED-IN TECHNICIAN
// ==================================================

$my_technician_id = 0;

if ($role === 'Technician') {

    $tech_stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM technicians
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $tech_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($tech_stmt);

    $tech_result = mysqli_stmt_get_result($tech_stmt);

    if ($tech = mysqli_fetch_assoc($tech_result)) {

        $my_technician_id = intval($tech['id']);

    }
}


// ==================================================
// ROLE FILTERS
// ==================================================

if ($role === 'Technician' && $my_technician_id > 0) {

    $request_filter =
        " WHERE technician_id = " .
        $my_technician_id;

    $job_filter =
        " WHERE technician_id = " .
        $my_technician_id;

} else {

    $request_filter = "";
    $job_filter = "";

}
// ==================================================
// TECHNICIAN MY WORK COUNTS
// ==================================================

$my_pending_requests = 0;
$my_progress_requests = 0;
$my_open_job_cards = 0;
$my_overdue_requests = 0;
$my_high_priority_requests = 0;

if ($role === 'Technician' && $my_technician_id > 0) {

    // ----------------------------------------------
    // My Pending Requests
    // ----------------------------------------------

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE technician_id = ?
         AND status = 'Pending'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $my_technician_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    $my_pending_requests =
        (int)$row['total'];


    // ----------------------------------------------
    // My In Progress Requests
    // ----------------------------------------------

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE technician_id = ?
         AND status = 'In Progress'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $my_technician_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    $my_progress_requests =
        (int)$row['total'];


    // ----------------------------------------------
    // My Open Job Cards
    // ----------------------------------------------

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM job_cards
         WHERE technician_id = ?
         AND status IN ('Open', 'In Progress')"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $my_technician_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    $my_open_job_cards =
        (int)$row['total'];


    // ----------------------------------------------
    // My Overdue Requests
    // ----------------------------------------------

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE technician_id = ?
         AND status IN ('Pending', 'In Progress')
         AND request_date < DATE_SUB(
             NOW(),
             INTERVAL 3 DAY
         )"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $my_technician_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    $my_overdue_requests =
        (int)$row['total'];


    // ----------------------------------------------
    // My High Priority Requests
    // ----------------------------------------------

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE technician_id = ?
         AND priority = 'High'
         AND status != 'Completed'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $my_technician_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    $my_high_priority_requests =
        (int)$row['total'];

}


// ==================================================
// CUSTOMERS
// ==================================================

$total_customers = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM customers"
    )
);


// ==================================================
// MACHINES
// ==================================================

$total_machines = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM machines"
    )
);


// ==================================================
// TECHNICIANS
// ==================================================

$total_technicians = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM technicians"
    )
);


// ==================================================
// PENDING REQUESTS
// ==================================================

$pending_requests = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status='Pending'"
    )
);


// ==================================================
// IN PROGRESS REQUESTS
// ==================================================

$progress_requests = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status='In Progress'"
    )
);


// ==================================================
// COMPLETED REQUESTS
// ==================================================

$completed_requests = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status='Completed'"
    )
);


// ==================================================
// TOTAL REQUESTS
// ==================================================

$total_requests = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter"
    )
);


// ==================================================
// TOTAL JOB CARDS
// ==================================================

$total_job_cards = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM job_cards
         $job_filter"
    )
);


// ==================================================
// OPEN JOB CARDS
// ==================================================

$open_job_cards = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM job_cards
         $job_filter
         " . ($job_filter ? " AND " : " WHERE ") . "
         status IN ('Open', 'In Progress')"
    )
);


// ==================================================
// AGING SERVICE REQUESTS
// ==================================================

$aging_requests = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status IN ('Pending', 'In Progress')
         AND request_date < DATE_SUB(CURDATE(), INTERVAL 3 DAY)"
    )
);

// ==============================
// High Priority Active Requests
// ==============================

$high_priority_requests = mysqli_query(
    $conn,
    "
    SELECT
        sr.id,
        sr.request_number,
        sr.priority,
        sr.status,
        sr.request_date,
        c.customer_name,
        m.asset_number,
        m.machine_model,
        t.full_name AS technician_name

    FROM service_requests sr

    LEFT JOIN customers c
        ON sr.customer_id = c.id

    LEFT JOIN machines m
        ON sr.machine_id = m.id

    LEFT JOIN technicians t
        ON sr.technician_id = t.id

    WHERE sr.priority = 'High'
    AND sr.status != 'Completed'

    ORDER BY sr.request_date ASC
    "
);

// ==============================
// Overdue Active Service Requests
// ==============================

$overdue_requests = mysqli_query(
    $conn,
    "
    SELECT
        sr.id,
        sr.request_number,
        sr.priority,
        sr.status,
        sr.request_date,

        c.customer_name,

        m.asset_number,
        m.machine_model,

        t.full_name AS technician_name

    FROM service_requests sr

    LEFT JOIN customers c
        ON sr.customer_id = c.id

    LEFT JOIN machines m
        ON sr.machine_id = m.id

    LEFT JOIN technicians t
        ON sr.technician_id = t.id

    WHERE sr.status IN ('Pending', 'In Progress')

    AND sr.request_date < DATE_SUB(
        NOW(),
        INTERVAL 3 DAY
    )

    ORDER BY sr.request_date ASC
    "
);


// ==================================================
// RECENT SERVICE REQUESTS
// ==================================================

$recent_requests = mysqli_query(
    $conn,
    "SELECT
        sr.request_number,
        m.asset_number,
        sr.status

     FROM service_requests sr

     INNER JOIN machines m
        ON sr.machine_id = m.id

     " . (
        $role === 'Technician'
        ? "WHERE sr.technician_id = $my_technician_id"
        : ""
     ) . "

     ORDER BY sr.id DESC

     LIMIT 5"
);


// ==================================================
// RECENT MACHINES
// ==================================================

$recent_machines = mysqli_query(
    $conn,
    "SELECT
        asset_number,
        machine_model

     FROM machines

     ORDER BY id DESC

     LIMIT 5"
);


// ==================================================
// RECENT JOB CARDS
// ==================================================

$recent_job_cards = mysqli_query(
    $conn,
    "SELECT
        jc.job_card_number,
        c.customer_name,
        t.full_name AS technician_name,
        jc.status

     FROM job_cards jc

     LEFT JOIN customers c
        ON jc.customer_id = c.id

     LEFT JOIN technicians t
        ON jc.technician_id = t.id

     " . (
        $role === 'Technician'
        ? "WHERE jc.technician_id = $my_technician_id"
        : ""
     ) . "

     ORDER BY jc.id DESC

     LIMIT 5"
);


// ==================================================
// TECHNICIAN WORKLOAD
// MANAGER ONLY
// ==================================================

$technician_workload = null;

if ($role === 'Manager') {

    $technician_workload = mysqli_query(
        $conn,
        "SELECT

            t.id,
            t.full_name,

            (
                SELECT COUNT(*)
                FROM service_requests sr
                WHERE sr.technician_id = t.id
                AND sr.status = 'Pending'
            ) AS pending_requests,

            (
                SELECT COUNT(*)
                FROM service_requests sr
                WHERE sr.technician_id = t.id
                AND sr.status = 'In Progress'
            ) AS progress_requests,

            (
                SELECT COUNT(*)
                FROM job_cards jc
                WHERE jc.technician_id = t.id
                AND jc.status IN ('Open', 'In Progress')
            ) AS open_job_cards

         FROM technicians t

         ORDER BY t.full_name ASC"
    );

}


// ==================================================
// REQUEST STATUS DATA FOR CHART
// ==================================================

$pending = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status='Pending'"
    )
);

$progress = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status='In Progress'"
    )
);

$completed = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         $request_filter
         " . ($request_filter ? " AND " : " WHERE ") . "
         status='Completed'"
    )
);

?>

<div class="main-content">

<h1>Dashboard</h1>

<h3>
    Welcome back,
    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
    👋
</h3>

<p>
    <?php if ($role === 'Technician'): ?>

        Here's your personal service workload.

    <?php elseif ($role === 'Reception'): ?>

        Here's today's customer service overview.

    <?php else: ?>

        Here's today's complete service overview.

    <?php endif; ?>
</p>


<!-- ==================================================
     DASHBOARD CARDS
================================================== -->

<div class="dashboard-cards">


    <!-- CUSTOMERS -->

    <a
        href="../customers/view_customers.php"
        class="card-link"
    >

        <div class="card">

            <h2>👥 Customers</h2>

            <h1 class="customers-count">

                <?php
                echo $total_customers['total'];
                ?>

            </h1>

        </div>

    </a>


    <!-- MACHINES -->

    <a
        href="../machines/view_machines.php"
        class="card-link"
    >

        <div class="card">

            <h2>🖨 Machines</h2>

            <h1 class="machines-count">

                <?php
                echo $total_machines['total'];
                ?>

            </h1>

        </div>

    </a>


    <!-- TECHNICIANS - MANAGER ONLY -->

    <?php if ($role === 'Manager'): ?>

    <a
        href="../technicians/view_technicians.php"
        class="card-link"
    >

        <div class="card">

            <h2>👨‍🔧 Technicians</h2>

            <h1 class="technicians-count">

                <?php
                echo $total_technicians['total'];
                ?>

            </h1>

        </div>

    </a>

    <?php endif; ?>


    <!-- PENDING -->

    <a
        href="../service_requests/view_requests.php?status=Pending"
        class="card-link"
    >

        <div class="card">

            <h2>
                🟡
                <?php
                echo ($role === 'Technician')
                    ? 'My Pending'
                    : 'Pending';
                ?>
            </h2>

            <h1 class="pending-count">

                <?php
                echo $pending_requests['total'];
                ?>

            </h1>

        </div>

    </a>


    <!-- IN PROGRESS -->

    <a
        href="../service_requests/view_requests.php?status=In+Progress"
        class="card-link"
    >

        <div class="card">

            <h2>
                🔵
                <?php
                echo ($role === 'Technician')
                    ? 'My In Progress'
                    : 'In Progress';
                ?>
            </h2>

            <h1 class="progress-count">

                <?php
                echo $progress_requests['total'];
                ?>

            </h1>

        </div>

    </a>


    <!-- COMPLETED -->

    <a
        href="../service_requests/view_requests.php?status=Completed"
        class="card-link"
    >

        <div class="card">

            <h2>
                🟢
                <?php
                echo ($role === 'Technician')
                    ? 'My Completed'
                    : 'Completed';
                ?>
            </h2>

            <h1 class="completed-count">

                <?php
                echo $completed_requests['total'];
                ?>

            </h1>

        </div>

    </a>


    <!-- JOB CARDS -->

    <a
        href="../job_cards/view_job_cards.php"
        class="card-link"
    >

        <div class="card">

            <h2>
                📋
                <?php
                echo ($role === 'Technician')
                    ? 'My Job Cards'
                    : 'Job Cards';
                ?>
            </h2>

            <h1 class="job-cards-count">

                <?php
                echo $total_job_cards['total'];
                ?>

            </h1>

        </div>

    </a>
   <!-- ==================================================
     TECHNICIAN EXTRA INFORMATION
     TECHNICIAN ONLY
================================================== -->

<?php if ($role === 'Technician'): ?>


    <!-- MY OVERDUE -->

    <a
    href="../service_requests/view_requests.php?aging=1"
    class="card-link"
    >

        <div class="card">

            <h2>
                ⏰ My Overdue
            </h2>

            <h1 class="overdue-count">

                <?php
                echo $my_overdue_requests;
                ?>

            </h1>

            <p>
                My Overdue Requests
            </p>

        </div>

    </a>


    <!-- MY HIGH PRIORITY -->

    <a
    href="../service_requests/view_requests.php?priority=High"
    class="card-link"
    >

        <div class="card">

            <h2>
                🔴 My High Priority
            </h2>

            <h1 class="high-priority-count">

                <?php
                echo $my_high_priority_requests;
                ?>

            </h1>

            <p>
                My High Priority Requests
            </p>

        </div>

    </a>


<?php endif; ?>


<!-- ==================================================
     UNREAD NOTIFICATIONS
================================================== -->

<a
    href="../notifications/index.php"
    class="card-link"
>

    <div class="card">

        <h2>
            🔔 Notifications
        </h2>

        <h1 class="notifications-count">

            <?php
            echo (int)$unread_notifications;
            ?>

        </h1>

        <p>
            Unread Notifications
        </p>

    </div>

</a>

</div>


<hr>

<br>


<!-- ==================================================
     CHARTS
================================================== -->

<div
    style="
        display:flex;
        justify-content:center;
        gap:30px;
        flex-wrap:wrap;
        margin-top:20px;
    "
>


    <!-- SYSTEM OVERVIEW -->

    <div
        class="card"
        style="
            flex:1;
            min-width:500px;
            max-width:650px;
            padding:20px;
        "
    >

        <h3>
            <?php
            echo ($role === 'Technician')
                ? 'My Service Overview'
                : 'System Overview';
            ?>
        </h3>

        <div style="height:300px;">

            <canvas id="overviewChart"></canvas>

        </div>

    </div>


    <!-- REQUEST STATUS -->

    <div
        class="card"
        style="
            flex:1;
            min-width:500px;
            max-width:650px;
            padding:20px;
        "
    >

        <h3>
            <?php
            echo ($role === 'Technician')
                ? 'My Service Requests'
                : 'Service Requests Status';
            ?>
        </h3>

        <div style="height:300px;">

            <canvas id="requestChart"></canvas>

        </div>

    </div>


</div>


<!-- ==================================================
     RECENT SERVICE REQUESTS
================================================== -->

<div class="dashboard-sections">

<div class="dashboard-box">

<h2>🛠 Recent Service Requests</h2>

<table>

<tr>

    <th>Request</th>

    <th>Machine</th>

    <th>Status</th>

</tr>


<?php if (mysqli_num_rows($recent_requests) > 0): ?>

<?php while ($row = mysqli_fetch_assoc($recent_requests)): ?>

<tr>

    <td>
        <?php
        echo htmlspecialchars(
            $row['request_number']
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row['asset_number']
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row['status']
        );
        ?>
    </td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

    <td
        colspan="3"
        style="text-align:center;"
    >

        No Service Requests Found.

    </td>

</tr>

<?php endif; ?>

</table>

</div>


<!-- ==================================================
     RECENT MACHINES
================================================== -->

<div class="dashboard-box">

<h2>🖨 Recently Added Machines</h2>

<table>

<tr>

    <th>Asset No.</th>

    <th>Model</th>

</tr>


<?php while ($row = mysqli_fetch_assoc($recent_machines)): ?>

<tr>

    <td>
        <?php
        echo htmlspecialchars(
            $row['asset_number']
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row['machine_model']
        );
        ?>
    </td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>


<!-- ==================================================
     RECENT JOB CARDS
================================================== -->

<div class="dashboard-sections">

<div class="dashboard-box">

<h2>📋 Recent Job Cards</h2>

<table>

<tr>

    <th>Job Card</th>

    <th>Customer</th>

    <th>Technician</th>

    <th>Status</th>

</tr>


<?php if (mysqli_num_rows($recent_job_cards) > 0): ?>

<?php while ($row = mysqli_fetch_assoc($recent_job_cards)): ?>

<tr>

    <td>
        <?php
        echo htmlspecialchars(
            $row['job_card_number']
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row['customer_name']
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row['technician_name']
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row['status']
        );
        ?>
    </td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

    <td
        colspan="4"
        style="text-align:center;"
    >

        No Job Cards Found.

    </td>

</tr>

<?php endif; ?>

</table>

</div>

</div>


<!-- ==================================================
     TECHNICIAN WORKLOAD
     MANAGER ONLY
================================================== -->

<?php if ($role === 'Manager'): ?>

<div class="dashboard-box">

    <h2>👨‍🔧 Technician Workload</h2>

    <p>
        Current active workload assigned to each technician.
    </p>

    <div style="overflow-x:auto;">

        <table>

            <tr>

                <th>Technician</th>

                <th>🟡 Pending</th>

                <th>🔵 In Progress</th>

                <th>📋 Open Job Cards</th>

                <th>📊 Total Active Work</th>

                <th>⚠️ Workload</th>

            </tr>


            <?php

            if (
                $technician_workload &&
                mysqli_num_rows(
                    $technician_workload
                ) > 0
            ):

            ?>

                <?php while (
                    $tech =
                    mysqli_fetch_assoc(
                        $technician_workload
                    )
                ):

                    $tech_pending =
                        (int)$tech['pending_requests'];

                    $tech_progress =
                        (int)$tech['progress_requests'];

                    $tech_open_cards =
                        (int)$tech['open_job_cards'];

                    $total_active =
                        $tech_pending +
                        $tech_progress +
                        $tech_open_cards;


                    /* ==============================
                       Determine Workload Level
                    ============================== */

                    if ($total_active >= 6) {

                        $workload_label = "Heavy";
                        $workload_icon = "🔴";

                    } elseif ($total_active >= 3) {

                        $workload_label = "Moderate";
                        $workload_icon = "🟡";

                    } else {

                        $workload_label = "Light";
                        $workload_icon = "🟢";

                    }

                ?>

                <tr>

                    <!-- Technician -->

                    <td>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $tech['full_name']
                            );
                            ?>

                        </strong>

                    </td>


                    <!-- Pending -->

                    <td>

                        <?php
                        echo $tech_pending;
                        ?>

                    </td>


                    <!-- In Progress -->

                    <td>

                        <?php
                        echo $tech_progress;
                        ?>

                    </td>


                    <!-- Open Job Cards -->

                    <td>

                        <?php
                        echo $tech_open_cards;
                        ?>

                    </td>


                    <!-- Total Active Work -->

                    <td>

                        <strong>

                            <?php
                            echo $total_active;
                            ?>

                        </strong>

                    </td>


                    <!-- Workload Level -->

                    <td>

                        <strong>

                            <?php
                            echo $workload_icon;
                            ?>

                            <?php
                            echo $workload_label;
                            ?>

                        </strong>

                    </td>

                </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td
                        colspan="6"
                        style="text-align:center;"
                    >

                        🟢 No Technician Workload Found.

                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

<?php endif; ?>


<!-- ==============================
     HIGH PRIORITY REQUESTS
================================ -->

<div class="dashboard-box">

    <h2>🔴 High Priority Requests</h2>

    <p>
        Active high-priority service requests requiring attention.
    </p>

    <div style="overflow-x:auto;">

        <table>

            <tr>
                <th>Request</th>
                <th>Customer</th>
                <th>Machine</th>
                <th>Technician</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>

            <?php if (mysqli_num_rows($high_priority_requests) > 0): ?>

                <?php while ($high = mysqli_fetch_assoc($high_priority_requests)): ?>

                    <tr>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $high['request_number']
                                );
                                ?>
                            </strong>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $high['customer_name']
                            );
                            ?>
                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $high['asset_number']
                            );
                            ?>

                            <br>

                            <small>
                                <?php
                                echo htmlspecialchars(
                                    $high['machine_model']
                                );
                                ?>
                            </small>

                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $high['technician_name']
                                ?? 'Unassigned'
                            );
                            ?>
                        </td>

                        <td>
                            🔴 High
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $high['status']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    $high['request_date']
                                )
                            );
                            ?>
                        </td>

                        <td>

                            <a
                                href="../service_requests/edit_request.php?id=<?php echo (int)$high['id']; ?>"
                                class="btn btn-edit"
                            >
                                👁️ View
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="8"
                        style="text-align:center;"
                    >
                        🟢 No active high-priority requests.
                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

<!-- ==============================
     OVERDUE SERVICE REQUESTS
================================ -->

<div class="dashboard-box">

    <h2>⏰ Overdue Service Requests</h2>

    <p>
        Service requests that have remained active for more than 3 days.
    </p>

    <div style="overflow-x:auto;">

        <table>

            <tr>
                <th>Request</th>
                <th>Customer</th>
                <th>Machine</th>
                <th>Technician</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Age</th>
                <th>Action</th>
            </tr>

            <?php if (mysqli_num_rows($overdue_requests) > 0): ?>

                <?php while ($overdue = mysqli_fetch_assoc($overdue_requests)): ?>

                    <?php
                    $request_date = strtotime(
                        $overdue['request_date']
                    );

                    $days_old = floor(
                        (time() - $request_date) / 86400
                    );
                    ?>

                    <tr>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $overdue['request_number']
                                );
                                ?>
                            </strong>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $overdue['customer_name']
                            );
                            ?>
                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $overdue['asset_number']
                            );
                            ?>

                            <br>

                            <small>
                                <?php
                                echo htmlspecialchars(
                                    $overdue['machine_model']
                                );
                                ?>
                            </small>

                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $overdue['technician_name']
                                ?? 'Unassigned'
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $overdue['priority']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $overdue['status']
                            );
                            ?>
                        </td>

                        <td>
                            🔴 <?php echo $days_old; ?> days
                        </td>

                        <td>

                            <a
                                href="../service_requests/edit_request.php?id=<?php echo (int)$overdue['id']; ?>"
                                class="btn btn-edit"
                            >
                                👁️ View
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="8"
                        style="text-align:center;"
                    >
                        🟢 No overdue service requests.
                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>


<!-- ==================================================
     ATTENTION REQUIRED
================================================== -->

<div class="dashboard-box attention-section">

<h2>⚠️ Attention Required</h2>

<div class="attention-cards">


    <!-- PENDING -->

    <a
        href="../service_requests/view_requests.php?status=Pending"
        class="attention-card"
    >

        <div class="attention-icon">
            🟡
        </div>

        <div class="attention-info">

            <h3>

                <?php
                echo ($role === 'Technician')
                    ? 'My Pending Requests'
                    : 'Pending Requests';
                ?>

            </h3>

            <h1>

                <?php
                echo $pending_requests['total'];
                ?>

            </h1>

            <p>
                Awaiting attention
            </p>

        </div>

    </a>


    <!-- IN PROGRESS -->

    <a
        href="../service_requests/view_requests.php?status=In+Progress"
        class="attention-card"
    >

        <div class="attention-icon">
            🔵
        </div>

        <div class="attention-info">

            <h3>

                <?php
                echo ($role === 'Technician')
                    ? 'My In Progress'
                    : 'In Progress';
                ?>

            </h3>

            <h1>

                <?php
                echo $progress_requests['total'];
                ?>

            </h1>

            <p>
                Currently being handled
            </p>

        </div>

    </a>


    <!-- OPEN JOB CARDS -->

    <a
        href="../job_cards/view_job_cards.php?status=open"
        class="attention-card"
    >

        <div class="attention-icon">
            📋
        </div>

        <div class="attention-info">

            <h3>

                <?php
                echo ($role === 'Technician')
                    ? 'My Open Job Cards'
                    : 'Open Job Cards';
                ?>

            </h3>

            <h1>

                <?php
                echo $open_job_cards['total'];
                ?>

            </h1>

            <p>
                Require completion
            </p>

        </div>

    </a>


    <!-- AGING -->

    <a
        href="../service_requests/view_requests.php?aging=1"
        class="attention-card"
    >

        <div class="attention-icon">
            ⚠️
        </div>

        <div class="attention-info">

            <h3>

                <?php
                echo ($role === 'Technician')
                    ? 'My Aging Requests'
                    : 'Aging Requests';
                ?>

            </h3>

            <h1>

                <?php
                echo $aging_requests['total'];
                ?>

            </h1>

            <p>
                Older than 3 days
            </p>

        </div>

    </a>


</div>

</div>


</div>


<!-- ==================================================
     CHART.JS
================================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>


// ==================================================
// SYSTEM / PERSONAL OVERVIEW
// ==================================================

const overviewCanvas =
    document.getElementById('overviewChart');


if (overviewCanvas) {

    <?php if ($role === 'Manager'): ?>

    new Chart(
        overviewCanvas,
        {

            type: 'doughnut',

            data: {

                labels: [
                    'Customers',
                    'Machines',
                    'Technicians',
                    'Requests'
                ],

                datasets: [{

                    data: [

                        <?php
                        echo (int)
                            $total_customers['total'];
                        ?>,

                        <?php
                        echo (int)
                            $total_machines['total'];
                        ?>,

                        <?php
                        echo (int)
                            $total_technicians['total'];
                        ?>,

                        <?php
                        echo (int)
                            $total_requests['total'];
                        ?>

                    ]

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        position: 'bottom'

                    }

                }

            }

        }
    );

    <?php else: ?>

    new Chart(
        overviewCanvas,
        {

            type: 'doughnut',

            data: {

                labels: [
                    'Pending',
                    'In Progress',
                    'Completed'
                ],

                datasets: [{

                    data: [

                        <?php
                        echo (int)
                            $pending_requests['total'];
                        ?>,

                        <?php
                        echo (int)
                            $progress_requests['total'];
                        ?>,

                        <?php
                        echo (int)
                            $completed_requests['total'];
                        ?>

                    ]

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        position: 'bottom'

                    }

                }

            }

        }
    );

    <?php endif; ?>

}


// ==================================================
// SERVICE REQUEST BAR CHART
// ==================================================

const requestCanvas =
    document.getElementById('requestChart');


if (requestCanvas) {

    new Chart(
        requestCanvas,
        {

            type: 'bar',

            data: {

                labels: [
                    'Pending',
                    'In Progress',
                    'Completed'
                ],

                datasets: [{

                    label: 'Requests',

                    data: [

                        <?php
                        echo (int)
                            $pending['total'];
                        ?>,

                        <?php
                        echo (int)
                            $progress['total'];
                        ?>,

                        <?php
                        echo (int)
                            $completed['total'];
                        ?>

                    ],

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    y: {

                        beginAtZero: true

                    }

                },

                plugins: {

                    legend: {

                        display: false

                    }

                }

            }

        }
    );

}

</script>


<?php

include("../includes/footer.php");

?>