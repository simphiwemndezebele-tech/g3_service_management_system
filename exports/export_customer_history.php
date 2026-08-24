<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


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
// GET SERVICE HISTORY
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
// EXCEL HEADERS
// ==================================================

$filename =
    "Customer_Service_History_" .
    preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $customer['customer_name']
    ) .
    ".xls";

header("Content-Type: application/vnd.ms-excel");
header(
    "Content-Disposition: attachment; filename=\"$filename\""
);
header("Pragma: no-cache");
header("Expires: 0");


// ==================================================
// EXCEL CONTENT
// ==================================================
?>

<html>

<head>

<meta charset="UTF-8">

</head>

<body>


<h2>G3 Systems</h2>

<h3>Customer Service History</h3>


<table border="1">

<tr>

    <td><strong>Customer</strong></td>

    <td>
        <?php
        echo htmlspecialchars(
            $customer['customer_name']
        );
        ?>
    </td>

</tr>


<tr>

    <td><strong>Company</strong></td>

    <td>
        <?php
        echo htmlspecialchars(
            $customer['company_name']
        );
        ?>
    </td>

</tr>


<tr>

    <td><strong>Phone</strong></td>

    <td>
        <?php
        echo htmlspecialchars(
            $customer['phone']
        );
        ?>
    </td>

</tr>


<tr>

    <td><strong>Email</strong></td>

    <td>
        <?php
        echo htmlspecialchars(
            $customer['email']
        );
        ?>
    </td>

</tr>

</table>


<br>


<table border="1">

<tr>

    <th>Request No.</th>
    <th>Machine</th>
    <th>Model</th>
    <th>Issue</th>
    <th>Priority</th>
    <th>Technician</th>
    <th>Request Status</th>
    <th>Job Card</th>
    <th>Work Done</th>
    <th>Remarks</th>
    <th>Date</th>

</tr>


<?php

while ($row = mysqli_fetch_assoc($result)) {

?>

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
    $row['asset_number'] ?? 'N/A'
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['machine_model'] ?? 'N/A'
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['issue_description']
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['priority']
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['technician_name'] ?? 'Unassigned'
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['request_status']
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['job_card_number'] ?? 'Not Generated'
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['work_done'] ?? '—'
);
?>
</td>


<td>
<?php
echo htmlspecialchars(
    $row['remarks'] ?? '—'
);
?>
</td>


<td>

<?php

if (!empty($row['request_date'])) {

    echo date(
        "d M Y",
        strtotime($row['request_date'])
    );

}

?>

</td>


</tr>

<?php

}

?>

</table>


</body>

</html>