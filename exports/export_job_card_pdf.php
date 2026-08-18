<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once("../config/db.php");
require_once("../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;


/* =====================================================
   COMPANY SETTINGS
===================================================== */

$settings_result = mysqli_query(
    $conn,
    "SELECT * FROM settings WHERE id=1"
);

$settings = mysqli_fetch_assoc($settings_result);

$company_name =
    $settings['company_name'] ?? "G3 Systems";

$company_phone =
    $settings['company_phone'] ?? "";

$company_email =
    $settings['company_email'] ?? "";

$company_address =
    $settings['company_address'] ?? "";


/* =====================================================
   COMPANY LOGO
===================================================== */

$logo_path = __DIR__ . "/../assets/images/logo.png";

$logo_base64 = "";

if (file_exists($logo_path)) {

    $logo_data = file_get_contents($logo_path);

    $logo_base64 =
        'data:image/png;base64,' .
        base64_encode($logo_data);
}


/* =====================================================
   SEARCH
===================================================== */

$search = "";

if (isset($_GET['search'])) {

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );
}


/* =====================================================
   JOB CARDS
===================================================== */

$sql = "SELECT

            job_cards.*,

            customers.customer_name,

            machines.asset_number,

            technicians.full_name

        FROM job_cards

        LEFT JOIN customers
        ON job_cards.customer_id = customers.id

        LEFT JOIN machines
        ON job_cards.machine_id = machines.id

        LEFT JOIN technicians
        ON job_cards.technician_id = technicians.id

        WHERE

        job_cards.job_card_number LIKE '%$search%'

        OR customers.customer_name LIKE '%$search%'

        OR machines.asset_number LIKE '%$search%'

        OR technicians.full_name LIKE '%$search%'

        OR job_cards.status LIKE '%$search%'

        ORDER BY job_cards.created_at DESC";


$result = mysqli_query($conn, $sql);


/* =====================================================
   TOTAL JOB CARDS
===================================================== */

$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM job_cards"
);

$totalJobCards = mysqli_fetch_assoc($total_query);


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
    margin: 30px 25px;
}

body {

    font-family: DejaVu Sans, sans-serif;

    font-size: 10px;

    color: #222;

}

.company-header {

    text-align: center;

    margin-bottom: 15px;

}

.logo {

    width: 90px;

    height: auto;

    margin-bottom: 5px;

}

.company-name {

    font-size: 20px;

    font-weight: bold;

    margin: 3px 0;

}

.system-name {

    font-size: 14px;

    margin: 3px 0;

}

.company-info {

    font-size: 9px;

    margin: 2px 0;

}

.header-line {

    border: 0;

    border-top: 1px solid #333;

    margin-top: 10px;

}

.report-title {

    text-align: center;

    margin-top: 15px;

    margin-bottom: 12px;

}

.report-title h2 {

    font-size: 18px;

    margin-bottom: 5px;

}

.report-info {

    text-align: center;

    font-size: 9px;

}

.summary {

    border: 1px solid #999;

    padding: 8px;

    margin-bottom: 15px;

}

table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 8px;

}

th {

    background-color: #eaeaea;

    font-weight: bold;

}

th, td {

    border: 1px solid #777;

    padding: 6px;

    text-align: left;

}

.footer {

    margin-top: 20px;

    text-align: center;

    font-size: 8px;

    color: #666;

}

</style>

</head>

<body>


<div class="company-header">
';


/* =====================================================
   LOGO
===================================================== */

if ($logo_base64 != "") {

    $html .= '

    <img
        src="' . $logo_base64 . '"
        class="logo"
    >

    ';

}


/* =====================================================
   COMPANY INFORMATION
===================================================== */

$html .= '

<div class="company-name">
    ' . htmlspecialchars($company_name) . '
</div>

<div class="system-name">
    Service Management System
</div>
';


if ($company_phone != "") {

    $html .= '

    <div class="company-info">
        Phone: ' .
        htmlspecialchars($company_phone) .
    '</div>

    ';

}


if ($company_email != "") {

    $html .= '

    <div class="company-info">
        Email: ' .
        htmlspecialchars($company_email) .
    '</div>

    ';

}


if ($company_address != "") {

    $html .= '

    <div class="company-info">
        Address: ' .
        htmlspecialchars($company_address) .
    '</div>

    ';

}


$html .= '

<hr class="header-line">

</div>


<div class="report-title">

<h2>Job Card Report</h2>

<div class="report-info">

<strong>Generated On:</strong>

' . date("d F Y h:i A") . '

<br>

<strong>Generated By:</strong>

' . htmlspecialchars($_SESSION['full_name']) . '

</div>

</div>


<div class="summary">

<strong>Total Job Cards:</strong>

' . (int)$totalJobCards['total'];


if ($search != "") {

    $html .= '

    <br>

    <strong>Search Filter:</strong>

    ' . htmlspecialchars($search);

}


$html .= '

</div>


<table>

<thead>

<tr>

<th>Job Card</th>

<th>Customer</th>

<th>Asset No.</th>

<th>Technician</th>

<th>Status</th>

<th>Created</th>

</tr>

</thead>

<tbody>
';


/* =====================================================
   JOB CARD DATA
===================================================== */

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $created_date = "";

        if (!empty($row['created_at'])) {

            $created_date = date(
                "d M Y",
                strtotime($row['created_at'])
            );

        }


        $html .= '

        <tr>

        <td>
            ' .
            htmlspecialchars(
                $row['job_card_number']
            ) .
        '
        </td>

        <td>
            ' .
            htmlspecialchars(
                $row['customer_name']
            ) .
        '
        </td>

        <td>
            ' .
            htmlspecialchars(
                $row['asset_number']
            ) .
        '
        </td>

        <td>
            ' .
            htmlspecialchars(
                $row['full_name']
            ) .
        '
        </td>

        <td>
            ' .
            htmlspecialchars(
                $row['status']
            ) .
        '
        </td>

        <td>
            ' .
            htmlspecialchars(
                $created_date
            ) .
        '
        </td>

        </tr>

        ';

    }

} else {

    $html .= '

    <tr>

        <td
            colspan="6"
            style="text-align:center;"
        >

            No Job Cards Found.

        </td>

    </tr>

    ';

}


$html .= '

</tbody>

</table>


<div class="footer">

' .
htmlspecialchars($company_name) .
' - Job Card Report

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


$dompdf = new Dompdf($options);


/* =====================================================
   LOAD HTML
===================================================== */

$dompdf->loadHtml($html);


/* =====================================================
   PAPER
===================================================== */

$dompdf->setPaper(
    'A4',
    'landscape'
);


/* =====================================================
   RENDER
===================================================== */

$dompdf->render();


/* =====================================================
   DOWNLOAD
===================================================== */

$dompdf->stream(
    "Job_Card_Report.pdf",
    [
        "Attachment" => true
    ]
);

exit();

?>