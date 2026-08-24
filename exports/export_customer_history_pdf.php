<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

require_once("../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;


// ==================================================
// GET CUSTOMER ID
// ==================================================

$customer_id = intval($_GET['id'] ?? 0);

if ($customer_id <= 0) {
    die("Invalid customer.");
}


// ==================================================
// GET CUSTOMER
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
// COMPANY SETTINGS
// ==================================================

$settings_result = mysqli_query(
    $conn,
    "SELECT *
     FROM settings
     WHERE id = 1"
);

$settings = mysqli_fetch_assoc($settings_result);

$company_name =
    $settings['company_name'] ?? 'G3 Systems';

$company_phone =
    $settings['company_phone'] ?? '';

$company_email =
    $settings['company_email'] ?? '';

$company_address =
    $settings['company_address'] ?? '';


// ==================================================
// CUSTOMER SERVICE HISTORY
// ==================================================

$sql = "
SELECT

    sr.request_number,
    sr.issue_description,
    sr.priority,
    sr.status AS request_status,
    sr.request_date,

    m.asset_number,
    m.machine_model,

    t.full_name AS technician_name,

    jc.job_card_number,
    jc.work_done,
    jc.remarks,
    jc.status AS job_status

FROM service_requests sr

LEFT JOIN machines m
    ON sr.machine_id = m.id

LEFT JOIN technicians t
    ON sr.technician_id = t.id

LEFT JOIN job_cards jc
    ON sr.id = jc.service_request_id

WHERE sr.customer_id = ?

ORDER BY sr.request_date DESC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $customer_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// ==================================================
// LOGO
// ==================================================

$logo_path =
    __DIR__ .
    "/../assets/images/logo.png";

$logo_data = '';

if (file_exists($logo_path)) {

    $logo_data =
        base64_encode(
            file_get_contents($logo_path)
        );

}


// ==================================================
// BUILD HTML
// ==================================================

$html = '

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<style>

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #222;
}

.header {
    text-align: center;
    margin-bottom: 15px;
}

.logo {
    width: 90px;
    margin-bottom: 5px;
}

.company-name {
    font-size: 18px;
    font-weight: bold;
}

.system-name {
    font-size: 12px;
    margin-bottom: 5px;
}

.company-info {
    font-size: 9px;
}

hr {
    border: 0;
    border-top: 1px solid #555;
    margin-top: 10px;
}

.title {
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    margin: 15px 0;
}

.customer-box {
    border: 1px solid #999;
    padding: 8px;
    margin-bottom: 12px;
}

.customer-box table {
    width: 100%;
    border-collapse: collapse;
}

.customer-box td {
    padding: 3px;
}

.history {
    width: 100%;
    border-collapse: collapse;
}

.history th {
    background-color: #eeeeee;
    border: 1px solid #555;
    padding: 5px;
    font-size: 8px;
}

.history td {
    border: 1px solid #777;
    padding: 5px;
    vertical-align: top;
    font-size: 7.5px;
}

.footer {
    margin-top: 20px;
    font-size: 8px;
    text-align: center;
}

</style>

</head>

<body>

<div class="header">
';


// ==================================================
// LOGO
// ==================================================

if (!empty($logo_data)) {

    $html .=
        '<img
            class="logo"
            src="data:image/png;base64,' .
        $logo_data .
        '">';

}


$html .= '

<div class="company-name">
    ' . htmlspecialchars($company_name) . '
</div>

<div class="system-name">
    Service Management System
</div>
';


// ==================================================
// COMPANY INFORMATION
// ==================================================

if (!empty($company_phone)) {

    $html .=
        '<div class="company-info">
            📞 ' .
        htmlspecialchars($company_phone) .
        '</div>';

}

if (!empty($company_email)) {

    $html .=
        '<div class="company-info">
            ✉ ' .
        htmlspecialchars($company_email) .
        '</div>';

}

if (!empty($company_address)) {

    $html .=
        '<div class="company-info">
            📍 ' .
        htmlspecialchars($company_address) .
        '</div>';

}


$html .= '

<hr>

</div>


<div class="title">
    CUSTOMER SERVICE HISTORY
</div>


<div class="customer-box">

<table>

<tr>

<td>
<strong>Customer:</strong>
</td>

<td>
' .
    htmlspecialchars(
        $customer['customer_name']
    ) .
    '
</td>

<td>
<strong>Company:</strong>
</td>

<td>
' .
    htmlspecialchars(
        $customer['company_name'] ?? ''
    ) .
    '
</td>

</tr>


<tr>

<td>
<strong>Phone:</strong>
</td>

<td>
' .
    htmlspecialchars(
        $customer['phone'] ?? ''
    ) .
    '
</td>

<td>
<strong>Email:</strong>
</td>

<td>
' .
    htmlspecialchars(
        $customer['email'] ?? ''
    ) .
    '
</td>

</tr>

</table>

</div>


<table class="history">

<tr>

<th>Request</th>
<th>Machine</th>
<th>Issue</th>
<th>Priority</th>
<th>Technician</th>
<th>Status</th>
<th>Job Card</th>
<th>Work Done</th>
<th>Remarks</th>
<th>Date</th>

</tr>
';


// ==================================================
// HISTORY ROWS
// ==================================================

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $machine =
            ($row['asset_number'] ?? 'N/A');

        if (!empty($row['machine_model'])) {

            $machine .=
                '<br>' .
                htmlspecialchars(
                    $row['machine_model']
                );

        }


        $technician =
            !empty($row['technician_name'])
            ? $row['technician_name']
            : 'Unassigned';


        $job_card =
            !empty($row['job_card_number'])
            ? $row['job_card_number']
            : 'Not Generated';


        $work_done =
            !empty($row['work_done'])
            ? $row['work_done']
            : '—';


        $remarks =
            !empty($row['remarks'])
            ? $row['remarks']
            : '—';


        $date = '';

        if (!empty($row['request_date'])) {

            $date =
                date(
                    "d M Y",
                    strtotime(
                        $row['request_date']
                    )
                );

        }


        $html .= '

<tr>

<td>
' .
            htmlspecialchars(
                $row['request_number']
            ) .
            '
</td>


<td>
' .
            $machine .
            '
</td>


<td>
' .
            nl2br(
                htmlspecialchars(
                    $row['issue_description']
                )
            ) .
            '
</td>


<td>
' .
            htmlspecialchars(
                $row['priority']
            ) .
            '
</td>


<td>
' .
            htmlspecialchars(
                $technician
            ) .
            '
</td>


<td>
' .
            htmlspecialchars(
                $row['request_status']
            ) .
            '
</td>


<td>
' .
            htmlspecialchars(
                $job_card
            ) .
            '
</td>


<td>
' .
            nl2br(
                htmlspecialchars(
                    $work_done
                )
            ) .
            '
</td>


<td>
' .
            nl2br(
                htmlspecialchars(
                    $remarks
                )
            ) .
            '
</td>


<td>
' .
            $date .
            '
</td>

</tr>

';

    }

} else {

    $html .= '

<tr>

<td colspan="10" style="text-align:center;">

No service history found.

</td>

</tr>

';

}


$html .= '

</table>


<div class="footer">

Generated by:
<strong>
' .
    htmlspecialchars(
        $_SESSION['full_name']
    ) .
    '
</strong>

<br>

Generated on:
' .
    date("d F Y h:i A") .
    '

<br><br>

G3 Systems Service Management System

</div>


</body>

</html>

';


// ==================================================
// DOMPDF CONFIGURATION
// ==================================================

$options = new Options();

$options->set(
    'isRemoteEnabled',
    true
);

$options->set(
    'isHtml5ParserEnabled',
    true
);


$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper(
    'A4',
    'landscape'
);

$dompdf->render();


// ==================================================
// DOWNLOAD PDF
// ==================================================

$filename =
    "Customer_Service_History_" .
    preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $customer['customer_name']
    ) .
    ".pdf";


$dompdf->stream(
    $filename,
    [
        "Attachment" => true
    ]
);

exit();

?>