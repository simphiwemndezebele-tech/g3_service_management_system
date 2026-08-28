<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");


/* ==================================================
   GET JOB CARD ID
================================================== */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


/* ==================================================
   GET JOB CARD
================================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        jc.*,

        c.customer_name,
        c.company_name,
        c.phone AS customer_phone,
        c.email AS customer_email,

        m.asset_number,
        m.machine_model,
        m.serial_number,
        m.ip_address,

        t.full_name AS technician_name,
        t.specialization,
        t.phone AS technician_phone,
        t.email AS technician_email

     FROM job_cards jc

     LEFT JOIN customers c
        ON jc.customer_id = c.id

     LEFT JOIN machines m
        ON jc.machine_id = m.id

     LEFT JOIN technicians t
        ON jc.technician_id = t.id

     WHERE jc.id = ?"
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


/* ==================================================
   TECHNICIAN SECURITY
================================================== */

if ($_SESSION['role'] === 'Technician') {

    $user_id = intval($_SESSION['user_id']);

    $security_stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM technicians
         WHERE id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $security_stmt,
        "ii",
        $row['technician_id'],
        $user_id
    );

    mysqli_stmt_execute($security_stmt);

    $security_result =
        mysqli_stmt_get_result($security_stmt);

    if (mysqli_num_rows($security_result) === 0) {

        header("Location: view_job_cards.php");
        exit();

    }
}

?>

<div class="main-content">

<h1>View Job Card</h1>


<!-- ==================================================
     ACTION BUTTONS
================================================== -->

<div style="margin-bottom:20px;">

<a
    href="view_job_cards.php"
    class="btn btn-add"
>
    ← Back
</a>


<a
    href="edit_job_card.php?id=<?php echo $row['id']; ?>"
    class="btn btn-edit"
>
    ✏️ Edit
</a>
<a
    href="print_job_card.php?id=<?php echo $row['id']; ?>"
    class="btn btn-search"
    target="_blank"
>
    🖨️ Print Job Card
</a>

<a
    href="download_job_card_pdf.php?id=<?php echo $row['id']; ?>"
    class="btn btn-add"
>
    📄 Download PDF
</a>

</div>


<!-- ==================================================
     JOB CARD HEADER
================================================== -->

<div class="dashboard-box">

<h2>📋 Service Job Card</h2>

<table>

<tr>

<th style="width:25%;">Job Card Number</th>

<td>
<strong>
<?php
echo htmlspecialchars(
    $row['job_card_number']
);
?>
</strong>
</td>

</tr>


<tr>

<th>Service Request</th>

<td>

<?php

if (
    isset($row['service_request_id']) &&
    $row['service_request_id']
) {

    echo "SR-" .
         str_pad(
             $row['service_request_id'],
             4,
             "0",
             STR_PAD_LEFT
         );

} else {

    echo "N/A";

}

?>

</td>

</tr>


<tr>

<th>Status</th>

<td>

<?php

if ($row['status'] === 'Open') {

    echo "<span class='badge badge-pending'>
            🟠 Open
          </span>";

} elseif ($row['status'] === 'In Progress') {

    echo "<span class='badge badge-progress'>
            🔵 In Progress
          </span>";

} else {

    echo "<span class='badge badge-completed'>
            🟢 Completed
          </span>";

}

?>

</td>

</tr>


<tr>

<th>Date Created</th>

<td>

<?php

if (!empty($row['created_at'])) {

    echo date(
        "d M Y H:i",
        strtotime($row['created_at'])
    );

} else {

    echo "N/A";

}

?>

</td>

</tr>

</table>

</div>


<br>


<!-- ==================================================
     CUSTOMER INFORMATION
================================================== -->

<div class="dashboard-box">

<h2>👤 Customer Information</h2>

<table>

<tr>

<th>Customer Name</th>

<td>
<?php
echo htmlspecialchars(
    $row['customer_name'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Company</th>

<td>
<?php
echo htmlspecialchars(
    $row['company_name'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Phone</th>

<td>
<?php
echo htmlspecialchars(
    $row['customer_phone'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Email</th>

<td>
<?php
echo htmlspecialchars(
    $row['customer_email'] ?? 'N/A'
);
?>
</td>

</tr>

</table>

</div>


<br>


<!-- ==================================================
     MACHINE INFORMATION
================================================== -->

<div class="dashboard-box">

<h2>🖨 Machine Information</h2>

<table>

<tr>

<th>Asset Number</th>

<td>
<?php
echo htmlspecialchars(
    $row['asset_number'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Machine Model</th>

<td>
<?php
echo htmlspecialchars(
    $row['machine_model'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Serial Number</th>

<td>
<?php
echo htmlspecialchars(
    $row['serial_number'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>IP Address</th>

<td>
<?php
echo htmlspecialchars(
    $row['ip_address'] ?? 'N/A'
);
?>
</td>

</tr>

</table>

</div>


<br>


<!-- ==================================================
     TECHNICIAN INFORMATION
================================================== -->

<div class="dashboard-box">

<h2>👨‍🔧 Technician Information</h2>

<table>

<tr>

<th>Technician</th>

<td>
<?php
echo htmlspecialchars(
    $row['technician_name'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Specialization</th>

<td>
<?php
echo htmlspecialchars(
    $row['specialization'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Phone</th>

<td>
<?php
echo htmlspecialchars(
    $row['technician_phone'] ?? 'N/A'
);
?>
</td>

</tr>


<tr>

<th>Email</th>

<td>
<?php
echo htmlspecialchars(
    $row['technician_email'] ?? 'N/A'
);
?>
</td>

</tr>

</table>

</div>


<br>


<!-- ==================================================
     SERVICE REQUEST
================================================== -->

<div class="dashboard-box">

<h2>🔧 Service Request</h2>

<table>

<tr>

<th>Issue Description</th>

<td>

<?php

echo nl2br(
    htmlspecialchars(
        $row['issue_description'] ?? 'N/A'
    )
);

?>

</td>

</tr>

</table>

</div>


<br>


<!-- ==================================================
     WORK DONE
================================================== -->

<div class="dashboard-box">

<h2>🛠 Work Done</h2>

<div style="
    padding:15px;
    border:1px solid #ddd;
    border-radius:5px;
    min-height:100px;
">

<?php

if (!empty($row['work_done'])) {

    echo nl2br(
        htmlspecialchars(
            $row['work_done']
        )
    );

} else {

    echo "<span style='color:#777;'>
            No work has been recorded yet.
          </span>";

}

?>

</div>

</div>


<br>


<!-- ==================================================
     TECHNICIAN REMARKS
================================================== -->

<div class="dashboard-box">

<h2>📝 Technician Remarks</h2>

<div style="
    padding:15px;
    border:1px solid #ddd;
    border-radius:5px;
    min-height:80px;
">

<?php

if (!empty($row['remarks'])) {

    echo nl2br(
        htmlspecialchars(
            $row['remarks']
        )
    );

} else {

    echo "<span style='color:#777;'>
            No remarks recorded.
          </span>";

}

?>

</div>

</div>


<br>


<!-- ==================================================
     BOTTOM ACTIONS
================================================== -->

<div style="margin-top:20px;">

<a
    href="view_job_cards.php"
    class="btn btn-add"
>
    ← Back to Job Cards
</a>


<a
    href="edit_job_card.php?id=<?php echo $row['id']; ?>"
    class="btn btn-edit"
>
    ✏️ Edit Job Card
</a>

<a
    href="print_job_card.php?id=<?php echo $row['id']; ?>"
    class="btn btn-search"
    target="_blank"
>
    🖨️ Print Job Card
</a>

<a
    href="download_job_card_pdf.php?id=<?php echo $row['id']; ?>"
    class="btn btn-add"
>
    📄 Download PDF
</a>
</div>


</div>


<?php include("../includes/footer.php"); ?>