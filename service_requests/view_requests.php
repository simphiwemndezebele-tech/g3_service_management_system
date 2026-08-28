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

$search = "";
$status_filter = "";
$aging_filter = false;
$technician_id = null;


/* ==============================
   Get Technician ID
   ============================== */

if ($_SESSION['role'] === 'Technician') {

    $user_id = intval($_SESSION['user_id']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM technicians WHERE user_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $tech_result = mysqli_stmt_get_result($stmt);

    if ($tech = mysqli_fetch_assoc($tech_result)) {

        $technician_id = intval($tech['id']);

    }
}


/* ==============================
   Search
   ============================== */

if (isset($_GET['search'])) {

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );
}


/* ==============================
   Status Filter
   ============================== */

if (isset($_GET['status'])) {

    $allowed_statuses = [
        'Pending',
        'In Progress',
        'Completed'
    ];

    if (in_array($_GET['status'], $allowed_statuses)) {

        $status_filter = mysqli_real_escape_string(
            $conn,
            $_GET['status']
        );

    }
}


/* ==============================
   Aging Filter
   ============================== */

if (isset($_GET['aging']) && $_GET['aging'] == '1') {

    $aging_filter = true;

}


/* ==============================
   Base Query
   ============================== */

$sql = "SELECT
            sr.*,
            c.customer_name,
            m.asset_number,
            m.machine_model,
            t.full_name

        FROM service_requests sr

        INNER JOIN customers c
            ON sr.customer_id = c.id

        INNER JOIN machines m
            ON sr.machine_id = m.id

        INNER JOIN technicians t
            ON sr.technician_id = t.id

        WHERE 1=1";


/* ==============================
   Search Filter
   ============================== */

if ($search !== "") {

    $sql .= " AND (
                sr.request_number LIKE '%$search%'
                OR c.customer_name LIKE '%$search%'
                OR m.asset_number LIKE '%$search%'
                OR m.machine_model LIKE '%$search%'
                OR t.full_name LIKE '%$search%'
                OR sr.status LIKE '%$search%'
            )";

}


/* ==============================
   Status Filter
   ============================== */

if ($status_filter !== "") {

    $sql .= " AND sr.status = '$status_filter'";

}

/* ==============================
   Priority Filter
   ============================== */

$priority_filter = "";

if (isset($_GET['priority'])) {

    $allowed_priorities = [
        'High',
        'Medium',
        'Low'
    ];

    if (in_array($_GET['priority'], $allowed_priorities, true)) {

        $priority_filter = mysqli_real_escape_string(
            $conn,
            $_GET['priority']
        );

    }

}

if ($priority_filter !== "") {

    $sql .= " AND sr.priority = '$priority_filter'";

}

/* ==============================
   Aging Filter
   ============================== */

if ($aging_filter) {

    $sql .= " AND sr.status IN ('Pending', 'In Progress')
              AND sr.request_date <
                  DATE_SUB(CURDATE(), INTERVAL 3 DAY)";

}


/* ==============================
   Technician Security
   ============================== */

if ($_SESSION['role'] === 'Technician') {

    if ($technician_id !== null) {

        $sql .= " AND sr.technician_id = "
              . intval($technician_id);

    } else {

        // No technician record linked to this account
        $sql .= " AND 1=0";

    }

}


/* ==============================
   Ordering
   ============================== */

$sql .= " ORDER BY sr.id DESC";


$result = mysqli_query($conn, $sql);

?>

<div class="main-content">

<h1>Service Request Management</h1>

<form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">

<input
type="text"
name="search"
placeholder="Search..."
value="<?php echo $search;?>">

<button class="btn btn-search">
Search
</button>

<a href="view_requests.php" class="btn btn-add">
Clear
</a>

</form>

<p>

<a href="add_request.php" class="btn btn-add">

+ Add Service Request

</a>

</p>

<table>

<tr>

<th>No.</th>

<th>Request No.</th>

<th>Customer</th>

<th>Machine</th>

<th>Technician</th>

<th>Priority</th>

<th>Status</th>

<th>Date</th>

<th>Action</th>

</tr>

<?php

$no=1;

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $no++; ?></td>

<td><?php echo $row['request_number']; ?></td>

<td><?php echo $row['customer_name']; ?></td>

<td>

<?php

echo $row['asset_number']." - ".$row['machine_model'];

?>

</td>

<td><?php echo $row['full_name']; ?></td>

<td>

<?php

if($row['priority']=="High"){

    echo "<span class='badge badge-high'>High</span>";

}elseif($row['priority']=="Medium"){

    echo "<span class='badge badge-medium'>Medium</span>";

}else{

    echo "<span class='badge badge-low'>Low</span>";

}

?>

</td>

<td>

<?php

if($row['status']=="Pending"){

    echo "<span class='badge badge-pending'>Pending</span>";

}elseif($row['status']=="In Progress"){

    echo "<span class='badge badge-progress'>In Progress</span>";

}else{

    echo "<span class='badge badge-completed'>Completed</span>";

}

?>

</td>

<td><?php echo date("d M Y",strtotime($row['request_date'])); ?></td>

<td>

<a
href="edit_request.php?id=<?php echo $row['id'];?>"
class="btn btn-edit">

✏️ Edit

</a>

<a href="../job_cards/generate_job_card.php?id=<?php echo $row['id']; ?>" class="btn btn-add">
    📋 Generate
</a>

<a
href="delete_request.php?id=<?php echo $row['id'];?>"
class="btn btn-delete"
onclick="return confirm('Delete this request?')">

🗑️ Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

<?php
include("../includes/footer.php");
?>