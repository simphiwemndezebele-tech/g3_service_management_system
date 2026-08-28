<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once("../config/db.php");
require_once("../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;


/* =====================================================
   GET JOB CARD ID
===================================================== */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


/* =====================================================
   GET JOB CARD
===================================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        jc.*,

        c.customer_name,
        c.company_name,
        c.phone AS customer_phone,
        c.email AS customer_email,
        c.address AS customer_address,

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

$job = mysqli_fetch_assoc($result);


if (!$job) {
    die("Job Card not found.");
}


/* =====================================================
   TECHNICIAN SECURITY
===================================================== */

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

    mysqli_stmt_execute(
        $security_stmt
    );

    $security_result =
        mysqli_stmt_get_result(
            $security_stmt
        );

    if (
        mysqli_num_rows(
            $security_result
        ) === 0
    ) {

        die(
            "You are not authorized to view this Job Card."
        );

    }
}


/* =====================================================
   COMPANY SETTINGS
===================================================== */

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


/* =====================================================
   LOGO
===================================================== */

$logo_path =
    __DIR__ . "/../assets/images/logo.png";

$logo_base64 = "";

if (file_exists($logo_path)) {

    $logo_data =
        file_get_contents(
            $logo_path
        );

    $logo_base64 =
        'data:image/png;base64,' .
        base64_encode(
            $logo_data
        );
}


/* =====================================================
   STATUS
===================================================== */

$status_class = "status-open";

if ($job['status'] === "In Progress") {

    $status_class =
        "status-progress";

} elseif (
    $job['status'] === "Completed"
) {

    $status_class =
        "status-completed";

}


/* =====================================================
   PDF HTML
===================================================== */

$html = '

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<style>

@page {
    margin: 25px;
}

body {

    font-family:
        DejaVu Sans,
        sans-serif;

    font-size: 10px;

    color: #222;

}

.header {

    text-align: center;

    border-bottom:
        2px solid #123f70;

    padding-bottom: 12px;

    margin-bottom: 15px;

}

.logo {

    width: 75px;

    margin-bottom: 5px;

}

.company-name {

    font-size: 20px;

    font-weight: bold;

    color: #123f70;

}

.system-name {

    font-size: 12px;

    margin-top: 3px;

}

.company-info {

    font-size: 9px;

    margin-top: 3px;

}

.title {

    text-align: center;

    margin: 15px 0;

}

.title h1 {

    font-size: 20px;

    margin: 0;

    color: #123f70;

}

.job-number {

    font-size: 13px;

    font-weight: bold;

    margin-top: 5px;

}

.section {

    background: #123f70;

    color: white;

    font-size: 11px;

    font-weight: bold;

    padding: 7px;

    margin-top: 14px;

}

table {

    width: 100%;

    border-collapse: collapse;

}

td {

    border: 1px solid #ccc;

    padding: 7px;

    vertical-align: top;

}

.label {

    width: 25%;

    font-weight: bold;

    background: #f1f1f1;

}

.text-box {

    border: 1px solid #ccc;

    padding: 10px;

    min-height: 70px;

    white-space: pre-wrap;

}

.status {

    font-weight: bold;

}

.signature-table {

    margin-top: 45px;

}

.signature-table td {

    width: 50%;

    border: none;

    text-align: center;

    padding: 20px;

}

.signature-line {

    border-top: 1px solid #222;

    padding-top: 5px;

}

.footer {

    text-align: center;

    font-size: 8px;

    color: #666;

    margin-top: 25px;

    border-top: 1px solid #ccc;

    padding-top: 8px;

}

</style>

</head>

<body>


<!-- =====================================================
     COMPANY HEADER
===================================================== -->

<div class="header">
';


if ($logo_base64 !== "") {

    $html .= '

    <img
        src="' .
        $logo_base64 .
        '"
        class="logo"
    >

    ';

}


$html .= '

<div class="company-name">

' .
htmlspecialchars(
    $company_name
) .
'

</div>


<div class="system-name">

Service Management System

</div>
';


if ($company_phone !== "") {

    $html .= '

    <div class="company-info">

    Phone:
    ' .
    htmlspecialchars(
        $company_phone
    ) .
    '

    </div>

    ';

}


if ($company_email !== "") {

    $html .= '

    <div class="company-info">

    Email:
    ' .
    htmlspecialchars(
        $company_email
    ) .
    '

    </div>

    ';

}


if ($company_address !== "") {

    $html .= '

    <div class="company-info">

    Address:
    ' .
    htmlspecialchars(
        $company_address
    ) .
    '

    </div>

    ';

}


$html .= '

</div>


<!-- =====================================================
     TITLE
===================================================== -->

<div class="title">

<h1>

JOB CARD

</h1>

<div class="job-number">

' .
htmlspecialchars(
    $job['job_card_number']
) .
'

</div>

</div>


<!-- =====================================================
     JOB CARD INFORMATION
===================================================== -->

<div class="section">

Job Card Information

</div>


<table>

<tr>

<td class="label">
Job Card Number
</td>

<td>
' .
htmlspecialchars(
    $job['job_card_number']
) .
'
</td>

</tr>


<tr>

<td class="label">
Service Request
</td>

<td>
';


if (!empty($job['service_request_id'])) {

    $html .=
        "SR-" .
        str_pad(
            $job['service_request_id'],
            4,
            "0",
            STR_PAD_LEFT
        );

} else {

    $html .= "N/A";

}


$html .= '

</td>

</tr>


<tr>

<td class="label">
Status
</td>

<td class="status">

' .
htmlspecialchars(
    $job['status']
) .
'

</td>

</tr>


<tr>

<td class="label">
Date Created
</td>

<td>

' .
date(
    "d F Y H:i",
    strtotime(
        $job['created_at']
    )
) .
'

</td>

</tr>


<tr>

<td class="label">
Technician
</td>

<td>

' .
htmlspecialchars(
    $job['technician_name']
    ?? "Not Assigned"
) .
'

</td>

</tr>

</table>


<!-- =====================================================
     CUSTOMER
===================================================== -->

<div class="section">

Customer Information

</div>


<table>

<tr>

<td class="label">
Customer Name
</td>

<td>
' .
htmlspecialchars(
    $job['customer_name']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Company
</td>

<td>
' .
htmlspecialchars(
    $job['company_name']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Phone
</td>

<td>
' .
htmlspecialchars(
    $job['customer_phone']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Email
</td>

<td>
' .
htmlspecialchars(
    $job['customer_email']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Address
</td>

<td>
' .
htmlspecialchars(
    $job['customer_address']
    ?? "N/A"
) .
'
</td>

</tr>

</table>


<!-- =====================================================
     MACHINE
===================================================== -->

<div class="section">

Machine Information

</div>


<table>

<tr>

<td class="label">
Asset Number
</td>

<td>
' .
htmlspecialchars(
    $job['asset_number']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Machine Model
</td>

<td>
' .
htmlspecialchars(
    $job['machine_model']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Serial Number
</td>

<td>
' .
htmlspecialchars(
    $job['serial_number']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
IP Address
</td>

<td>
' .
htmlspecialchars(
    $job['ip_address']
    ?? "N/A"
) .
'
</td>

</tr>

</table>


<!-- =====================================================
     TECHNICIAN
===================================================== -->

<div class="section">

Technician Information

</div>


<table>

<tr>

<td class="label">
Technician
</td>

<td>
' .
htmlspecialchars(
    $job['technician_name']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Specialization
</td>

<td>
' .
htmlspecialchars(
    $job['specialization']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Phone
</td>

<td>
' .
htmlspecialchars(
    $job['technician_phone']
    ?? "N/A"
) .
'
</td>

</tr>


<tr>

<td class="label">
Email
</td>

<td>
' .
htmlspecialchars(
    $job['technician_email']
    ?? "N/A"
) .
'
</td>

</tr>

</table>


<!-- =====================================================
     ISSUE
===================================================== -->

<div class="section">

Issue / Service Description

</div>


<div class="text-box">

' .
nl2br(
    htmlspecialchars(
        $job['issue_description']
        ?? "N/A"
    )
) .
'

</div>


<!-- =====================================================
     WORK DONE
===================================================== -->

<div class="section">

Work Done

</div>


<div class="text-box">

';


if (!empty($job['work_done'])) {

    $html .= nl2br(
        htmlspecialchars(
            $job['work_done']
        )
    );

} else {

    $html .=
        "No work has been recorded yet.";

}


$html .= '

</div>


<!-- =====================================================
     REMARKS
===================================================== -->

<div class="section">

Technician Remarks

</div>


<div class="text-box">

';


if (!empty($job['remarks'])) {

    $html .= nl2br(
        htmlspecialchars(
            $job['remarks']
        )
    );

} else {

    $html .=
        "No remarks recorded.";

}


$html .= '

</div>


<!-- =====================================================
     SIGNATURES
===================================================== -->

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


<!-- =====================================================
     FOOTER
===================================================== -->

<div class="footer">

' .
htmlspecialchars(
    $company_name
) .
'

<br>

Service Management System

<br>

Job Card:
' .
htmlspecialchars(
    $job['job_card_number']
) .
'

</div>


</body>

</html>
';


/* =====================================================
   DOMPDF CONFIGURATION
===================================================== */

$options = new Options();

$options->set(
    'isRemoteEnabled',
    true
);

$options->set(
    'isHtml5ParserEnabled',
    true
);

$options->set(
    'defaultFont',
    'DejaVu Sans'
);


$dompdf = new Dompdf(
    $options
);


/* =====================================================
   LOAD HTML
===================================================== */

$dompdf->loadHtml(
    $html
);


/* =====================================================
   PAPER
===================================================== */

$dompdf->setPaper(
    'A4',
    'portrait'
);


/* =====================================================
   RENDER
===================================================== */

$dompdf->render();


/* =====================================================
   DOWNLOAD
===================================================== */

$filename =
    $job['job_card_number'] .
    ".pdf";


$dompdf->stream(
    $filename,
    [
        "Attachment" => true
    ]
);

exit();

?>