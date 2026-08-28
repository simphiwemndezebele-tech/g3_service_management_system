<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once("../config/db.php");

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


/* ==================================================
   GET JOB CARD INFORMATION
================================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        jc.id,
        jc.job_card_number,
        jc.service_request_id,
        jc.issue_description,
        jc.work_done,
        jc.remarks,
        jc.status,
        jc.created_at,

        c.customer_name,
        c.company_name,
        c.phone,
        c.email,
        c.address,

        m.asset_number,
        m.machine_model,

        t.full_name AS technician_name

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

$job = mysqli_fetch_assoc($result);


if (!$job) {
    die("Job Card not found.");
}


/* ==================================================
   TECHNICIAN SECURITY
================================================== */

if ($_SESSION['role'] === 'Technician') {

    $user_id = intval($_SESSION['user_id']);

    $security_stmt = mysqli_prepare(
        $conn,
        "SELECT jc.id
         FROM job_cards jc
         INNER JOIN technicians t
            ON jc.technician_id = t.id
         WHERE jc.id = ?
         AND t.user_id = ?"
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

        die("You are not authorized to view this Job Card.");

    }
}


/* ==================================================
   COMPANY SETTINGS
================================================== */

$settings_result = mysqli_query(
    $conn,
    "SELECT * FROM settings WHERE id = 1"
);

$settings = mysqli_fetch_assoc(
    $settings_result
);

$company_name =
    $settings['company_name']
    ?? "G3 Systems (pty) Ltd";

$company_phone =
    $settings['company_phone']
    ?? "";

$company_email =
    $settings['company_email']
    ?? "";

$company_address =
    $settings['company_address']
    ?? "";


/* ==================================================
   LOGO
================================================== */

$logo_path =
    "../assets/images/logo.png";

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>
<?php
echo htmlspecialchars(
    $job['job_card_number']
);
?>
</title>


<style>

body {

    font-family: Arial, sans-serif;

    background: #f2f2f2;

    margin: 0;

    padding: 30px;

    color: #222;

}


.job-card {

    background: white;

    max-width: 900px;

    margin: auto;

    padding: 35px;

    box-shadow:
        0 0 10px
        rgba(0,0,0,0.15);

}


.company-header {

    text-align: center;

    border-bottom:
        2px solid #123f70;

    padding-bottom: 15px;

    margin-bottom: 20px;

}


.company-logo {

    width: 90px;

    margin-bottom: 5px;

}


.company-name {

    font-size: 24px;

    font-weight: bold;

    color: #123f70;

}


.system-name {

    font-size: 15px;

    margin-top: 4px;

}


.company-info {

    font-size: 12px;

    margin-top: 5px;

}


.document-title {

    text-align: center;

    margin: 20px 0;

}


.document-title h1 {

    margin: 0;

    font-size: 24px;

    color: #123f70;

}


.job-number {

    margin-top: 8px;

    font-size: 16px;

    font-weight: bold;

}


.section-title {

    background: #123f70;

    color: white;

    padding: 8px 10px;

    font-weight: bold;

    margin-top: 20px;

}


.info-table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 0;

}


.info-table td {

    border: 1px solid #ccc;

    padding: 9px;

}


.label {

    width: 25%;

    font-weight: bold;

    background: #f3f3f3;

}


.work-box {

    border: 1px solid #ccc;

    min-height: 100px;

    padding: 12px;

    white-space: pre-wrap;

}


.status {

    display: inline-block;

    padding: 6px 14px;

    border-radius: 4px;

    font-weight: bold;

}


.status-open {

    background: #fff0d6;

    color: #b56a00;

}


.status-progress {

    background: #dcecff;

    color: #1256a3;

}


.status-completed {

    background: #dff7e7;

    color: #16803c;

}


.signature-table {

    width: 100%;

    margin-top: 50px;

    border-collapse: collapse;

}


.signature-table td {

    width: 50%;

    padding: 20px;

    text-align: center;

}


.signature-line {

    border-top: 1px solid #222;

    margin-top: 40px;

    padding-top: 5px;

}


.footer {

    text-align: center;

    margin-top: 30px;

    padding-top: 10px;

    border-top: 1px solid #ccc;

    font-size: 11px;

    color: #666;

}


.print-buttons {

    text-align: center;

    margin-bottom: 20px;

}


.btn {

    display: inline-block;

    padding: 10px 18px;

    margin: 5px;

    text-decoration: none;

    border-radius: 4px;

    font-weight: bold;

}


.btn-print {

    background: #123f70;

    color: white;

}


.btn-back {

    background: #777;

    color: white;

}


/* ==================================================
   PRINT
================================================== */

@media print {

    body {

        background: white;

        padding: 0;

    }

    .job-card {

        box-shadow: none;

        max-width: none;

        padding: 15px;

    }

    .print-buttons {

        display: none;

    }

}

</style>

</head>


<body>


<div class="print-buttons">

<a
href="view_job_card.php?id=<?php echo $id; ?>"
class="btn btn-back">

← Back

</a>


<a
href="#"
onclick="window.print(); return false;"
class="btn btn-print">

🖨️ Print Job Card

</a>

</div>


<div class="job-card">


<!-- ==================================================
     COMPANY HEADER
================================================== -->

<div class="company-header">


<?php if (file_exists($logo_path)): ?>

<img
src="<?php echo $logo_path; ?>"
class="company-logo"
>

<?php endif; ?>


<div class="company-name">

<?php
echo htmlspecialchars(
    $company_name
);
?>

</div>


<div class="system-name">

Service Management System

</div>


<?php if ($company_phone !== ""): ?>

<div class="company-info">

Phone:
<?php
echo htmlspecialchars(
    $company_phone
);
?>

</div>

<?php endif; ?>


<?php if ($company_email !== ""): ?>

<div class="company-info">

Email:
<?php
echo htmlspecialchars(
    $company_email
);
?>

</div>

<?php endif; ?>


<?php if ($company_address !== ""): ?>

<div class="company-info">

Address:
<?php
echo htmlspecialchars(
    $company_address
);
?>

</div>

<?php endif; ?>


</div>


<!-- ==================================================
     DOCUMENT TITLE
================================================== -->

<div class="document-title">

<h1>
JOB CARD
</h1>


<div class="job-number">

<?php
echo htmlspecialchars(
    $job['job_card_number']
);
?>

</div>

</div>


<!-- ==================================================
     JOB CARD INFORMATION
================================================== -->

<div class="section-title">

Job Card Information

</div>


<table class="info-table">


<tr>

<td class="label">
Job Card Number
</td>

<td>
<?php
echo htmlspecialchars(
    $job['job_card_number']
);
?>
</td>

</tr>


<tr>

<td class="label">
Date Created
</td>

<td>

<?php

echo date(
    "d F Y",
    strtotime(
        $job['created_at']
    )
);

?>

</td>

</tr>


<tr>

<td class="label">
Status
</td>

<td>

<?php

if ($job['status'] === "Open") {

    echo '<span class="status status-open">
            🟠 Open
          </span>';

} elseif (
    $job['status'] === "In Progress"
) {

    echo '<span class="status status-progress">
            🔵 In Progress
          </span>';

} else {

    echo '<span class="status status-completed">
            🟢 Completed
          </span>';

}

?>

</td>

</tr>


<tr>

<td class="label">
Technician
</td>

<td>

<?php
echo htmlspecialchars(
    $job['technician_name']
    ?? "Not Assigned"
);
?>

</td>

</tr>


</table>


<!-- ==================================================
     CUSTOMER INFORMATION
================================================== -->

<div class="section-title">

Customer Information

</div>


<table class="info-table">


<tr>

<td class="label">
Customer
</td>

<td>

<?php
echo htmlspecialchars(
    $job['customer_name']
);
?>

</td>

</tr>


<tr>

<td class="label">
Company
</td>

<td>

<?php
echo htmlspecialchars(
    $job['company_name']
    ?? ""
);
?>

</td>

</tr>


<tr>

<td class="label">
Phone
</td>

<td>

<?php
echo htmlspecialchars(
    $job['phone']
    ?? ""
);
?>

</td>

</tr>


<tr>

<td class="label">
Email
</td>

<td>

<?php
echo htmlspecialchars(
    $job['email']
    ?? ""
);
?>

</td>

</tr>


<tr>

<td class="label">
Address
</td>

<td>

<?php
echo htmlspecialchars(
    $job['address']
    ?? ""
);
?>

</td>

</tr>


</table>


<!-- ==================================================
     MACHINE INFORMATION
================================================== -->

<div class="section-title">

Machine Information

</div>


<table class="info-table">


<tr>

<td class="label">
Asset Number
</td>

<td>

<?php
echo htmlspecialchars(
    $job['asset_number']
);
?>

</td>

</tr>


<tr>

<td class="label">
Machine Model
</td>

<td>

<?php
echo htmlspecialchars(
    $job['machine_model']
);
?>

</td>

</tr>


</table>


<!-- ==================================================
     ISSUE DESCRIPTION
================================================== -->

<div class="section-title">

Issue / Service Description

</div>


<div class="work-box">

<?php

echo nl2br(
    htmlspecialchars(
        $job['issue_description']
    )
);

?>

</div>


<!-- ==================================================
     WORK DONE
================================================== -->

<div class="section-title">

Work Done

</div>


<div class="work-box">

<?php

if (!empty($job['work_done'])) {

    echo nl2br(
        htmlspecialchars(
            $job['work_done']
        )
    );

} else {

    echo "No work recorded yet.";

}

?>

</div>


<!-- ==================================================
     REMARKS
================================================== -->

<div class="section-title">

Technician Remarks

</div>


<div class="work-box">

<?php

if (!empty($job['remarks'])) {

    echo nl2br(
        htmlspecialchars(
            $job['remarks']
        )
    );

} else {

    echo "No remarks.";

}

?>

</div>


<!-- ==================================================
     SIGNATURES
================================================== -->

<table class="signature-table">

<tr>

<td>

<div class="signature-line">

Technician Signature

</div>

</td>


<td>

<div class="signature-line">

Customer Signature

</div>

</td>

</tr>

</table>


<!-- ==================================================
     FOOTER
================================================== -->

<div class="footer">

<?php
echo htmlspecialchars(
    $company_name
);
?>

<br>

Service Management System

<br>

Job Card:
<?php
echo htmlspecialchars(
    $job['job_card_number']
);
?>

</div>


</div>


</body>

</html>