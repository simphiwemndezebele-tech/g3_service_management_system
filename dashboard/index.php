<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

// Customers
$total_customers = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers")
);

// Machines
$total_machines = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM machines")
);

// Technicians
$total_technicians = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM technicians")
);

// Pending Requests
$pending_requests = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM service_requests WHERE status='Pending'")
);

// In Progress Requests
$progress_requests = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM service_requests WHERE status='In Progress'")
);

// Completed Requests
$completed_requests = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM service_requests WHERE status='Completed'")
);

// Recent Service Requests
$recent_requests = mysqli_query($conn, "

SELECT

sr.request_number,
m.asset_number,
sr.status

FROM service_requests sr

INNER JOIN machines m

ON sr.machine_id=m.id

ORDER BY sr.id DESC

LIMIT 5

");

// Recent Machines

$recent_machines = mysqli_query($conn, "

SELECT

asset_number,
machine_model

FROM machines

ORDER BY id DESC

LIMIT 5

");
?>

<div class="main-content">

<h1>Dashboard</h1>
<h3>Welcome back, <?php echo $_SESSION['full_name']; ?> 👋</h3>

<p>Here's today's service overview.</p>

<div class="dashboard-cards">

<a href="../customers/view_customers.php" class="card-link">
    <div class="card">
        <h2>👥 Customers</h2>
        <h1 class="customers-count"><?php echo $total_customers['total']; ?></h1>
    </div>
</a>

<a href="../machines/view_machines.php" class="card-link">
    <div class="card">
        <h2>🖨 Machines</h2>
        <h1 class="machines-count"><?php echo $total_machines['total']; ?></h1>
    </div>
</a>

<a href="../technicians/view_technicians.php" class="card-link">
    <div class="card">
        <h2>👨‍🔧 Technicians</h2>
        <h1 class="technicians-count"><?php echo $total_technicians['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=Pending" class="card-link">
    <div class="card">
        <h2>🟡 Pending</h2>
        <h1 class="pending-count"><?php echo $pending_requests['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=In+Progress" class="card-link">
    <div class="card">
        <h2>🔵 In Progress</h2>
        <h1 class="progress-count"><?php echo $progress_requests['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=Completed" class="card-link">
    <div class="card">
        <h2>🟢 Completed</h2>
        <h1 class="completed-count"><?php echo $completed_requests['total']; ?></h1>
    </div>
</a>

</div>
<div class="dashboard-sections">

<div class="dashboard-box">

<h2>🛠 Recent Service Requests</h2>

<table>

<tr>

<th>Request</th>

<th>Machine</th>

<th>Status</th>

</tr>

<?php while($row=mysqli_fetch_assoc($recent_requests)){ ?>

<tr>

<td><?php echo $row['request_number']; ?></td>

<td><?php echo $row['asset_number']; ?></td>

<td><?php echo $row['status']; ?></td>

</tr>

<?php } ?>

</table>

</div>

<div class="dashboard-box">

<h2>🖨 Recently Added Machines</h2>

<table>

<tr>

<th>Asset No.</th>

<th>Model</th>

</tr>

<?php while($row=mysqli_fetch_assoc($recent_machines)){ ?>

<tr>

<td><?php echo $row['asset_number']; ?></td>

<td><?php echo $row['machine_model']; ?></td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

<?php
include("../includes/footer.php");
?>