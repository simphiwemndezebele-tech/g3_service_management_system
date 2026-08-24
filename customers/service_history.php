<?php

session_start();

require_once("../includes/permissions.php");

requireRole(['Manager', 'Reception']);

if (!isset($_SESSION['username'])) {

    header("Location: ../auth/login.php");
    exit();

}

include("../config/db.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");


// ==================================================
// GET CUSTOMER ID
// ==================================================

$customer_id = intval($_GET['id'] ?? 0);

if ($customer_id <= 0) {

    die("Invalid customer.");

}


// ==================================================
// GET CUSTOMER INFORMATION
// ==================================================

$customer_stmt = mysqli_prepare(
    $conn,
    "SELECT *
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

$customer = mysqli_fetch_assoc($customer_result);


if (!$customer) {

    die("Customer not found.");

}


// ==================================================
// GET CUSTOMER SERVICE HISTORY
// ==================================================

$history_stmt = mysqli_prepare(
    $conn,
    "SELECT

        sr.id AS request_id,
        sr.request_number,
        sr.issue_description,
        sr.priority,
        sr.status AS request_status,
        sr.request_date,

        m.asset_number,
        m.machine_model,

        t.full_name AS technician_name,

        jc.id AS job_card_id,
        jc.job_card_number,
        jc.work_done,
        jc.remarks,
        jc.status AS job_status,
        jc.created_at AS job_created_at

     FROM service_requests sr

     LEFT JOIN machines m
        ON sr.machine_id = m.id

     LEFT JOIN technicians t
        ON sr.technician_id = t.id

     LEFT JOIN job_cards jc
        ON sr.id = jc.service_request_id

     WHERE sr.customer_id = ?

     ORDER BY sr.request_date DESC"
);

mysqli_stmt_bind_param(
    $history_stmt,
    "i",
    $customer_id
);

mysqli_stmt_execute($history_stmt);

$history_result =
    mysqli_stmt_get_result($history_stmt);


// ==================================================
// COUNT SERVICE REQUESTS
// ==================================================

$count_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM service_requests
     WHERE customer_id = ?"
);

mysqli_stmt_bind_param(
    $count_stmt,
    "i",
    $customer_id
);

mysqli_stmt_execute($count_stmt);

$count_result =
    mysqli_stmt_get_result($count_stmt);

$count_data =
    mysqli_fetch_assoc($count_result);

$total_requests =
    $count_data['total'];

?>

<div class="main-content">


<!-- ==================================================
     CUSTOMER HEADER
================================================== -->

<div class="report-header">

    <h1>Customer Service History</h1>

    <hr>

</div>


<!-- ==================================================
     CUSTOMER INFORMATION
================================================== -->

<div class="card">

    <h2>
        <?php
        echo htmlspecialchars(
            $customer['customer_name']
        );
        ?>
    </h2>


    <?php if (!empty($customer['company_name'])): ?>

        <p>

            <strong>Company:</strong>

            <?php
            echo htmlspecialchars(
                $customer['company_name']
            );
            ?>

        </p>

    <?php endif; ?>


    <?php if (!empty($customer['phone'])): ?>

        <p>

            <strong>Phone:</strong>

            <?php
            echo htmlspecialchars(
                $customer['phone']
            );
            ?>

        </p>

    <?php endif; ?>


    <?php if (!empty($customer['email'])): ?>

        <p>

            <strong>Email:</strong>

            <?php
            echo htmlspecialchars(
                $customer['email']
            );
            ?>

        </p>

    <?php endif; ?>


    <p>

        <strong>Total Service Requests:</strong>

        <?php echo $total_requests; ?>

    </p>

</div>


<br>


<!-- ==================================================
     ACTION BUTTONS
================================================== -->

<a
    href="view_customers.php"
    class="btn btn-search"
>

    ↩️ Back to Customers

</a>


<a
    href="#"
    onclick="window.print()"
    class="btn btn-add"
>

    🖨️ Print History

</a>
<a
    href="../exports/export_customer_history.php?id=<?php echo $customer_id; ?>"
    class="btn btn-add"
>
    📊 Export Excel
</a>
<a
    href="../exports/export_customer_history_pdf.php?id=<?php echo $customer_id; ?>"
    class="btn btn-add"
>
    📄 Export PDF
</a>


<br>
<br>


<!-- ==================================================
     SERVICE HISTORY TABLE
================================================== -->

<h2>Service History</h2>


<table
    border="1"
    cellpadding="10"
    cellspacing="0"
    width="100%"
>

<tr>

    <th>Request No.</th>

    <th>Machine</th>

    <th>Issue</th>

    <th>Priority</th>

    <th>Technician</th>

    <th>Request Status</th>

    <th>Job Card</th>

    <th>Work Done</th>

    <th>Remarks</th>

    <th>Date</th>

</tr>


<?php if (mysqli_num_rows($history_result) > 0): ?>


<?php while ($row = mysqli_fetch_assoc($history_result)): ?>


<tr>


<!-- Request Number -->

<td>

    <?php
    echo htmlspecialchars(
        $row['request_number']
    );
    ?>

</td>


<!-- Machine -->

<td>

    <?php

    if (!empty($row['asset_number'])) {

        echo htmlspecialchars(
            $row['asset_number']
        );

        echo "<br>";

        echo htmlspecialchars(
            $row['machine_model']
        );

    } else {

        echo "N/A";

    }

    ?>

</td>


<!-- Issue -->

<td>

    <?php
    echo nl2br(
        htmlspecialchars(
            $row['issue_description']
        )
    );
    ?>

</td>


<!-- Priority -->

<td>

    <?php
    echo htmlspecialchars(
        $row['priority']
    );
    ?>

</td>


<!-- Technician -->

<td>

    <?php

    echo !empty($row['technician_name'])
        ? htmlspecialchars(
            $row['technician_name']
        )
        : "Unassigned";

    ?>

</td>


<!-- Request Status -->

<td>

    <?php

    echo htmlspecialchars(
        $row['request_status']
    );

    ?>

</td>


<!-- Job Card -->

<td>

    <?php

    if (!empty($row['job_card_number'])) {

        echo htmlspecialchars(
            $row['job_card_number']
        );

    } else {

        echo "Not Generated";

    }

    ?>

</td>


<!-- Work Done -->

<td>

    <?php

    if (!empty($row['work_done'])) {

        echo nl2br(
            htmlspecialchars(
                $row['work_done']
            )
        );

    } else {

        echo "—";

    }

    ?>

</td>


<!-- Remarks -->

<td>

    <?php

    if (!empty($row['remarks'])) {

        echo nl2br(
            htmlspecialchars(
                $row['remarks']
            )
        );

    } else {

        echo "—";

    }

    ?>

</td>


<!-- Date -->

<td>

    <?php

    if (!empty($row['request_date'])) {

        echo date(
            "d M Y",
            strtotime(
                $row['request_date']
            )
        );

    } else {

        echo "—";

    }

    ?>

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

    <td
        colspan="10"
        style="text-align:center;"
    >

        No service history found for this customer.

    </td>

</tr>


<?php endif; ?>


</table>


</div>


<?php

include("../includes/footer.php");

?>