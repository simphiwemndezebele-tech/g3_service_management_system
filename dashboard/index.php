<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");

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
?>

<div class="main-content">

<h1>Dashboard</h1>

<p>Welcome to G3 Service Management System Version 2.0</p>

<div class="dashboard-cards">

<a href="../customers/view_customers.php" class="card-link">
    <div class="card">
        <h2>👥 Customers</h2>
        <h1><?php echo $total_customers['total']; ?></h1>
    </div>
</a>

<a href="../machines/view_machines.php" class="card-link">
    <div class="card">
        <h2>🖨 Machines</h2>
        <h1><?php echo $total_machines['total']; ?></h1>
    </div>
</a>

<a href="../technicians/view_technicians.php" class="card-link">
    <div class="card">
        <h2>👨‍🔧 Technicians</h2>
        <h1><?php echo $total_technicians['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=Pending" class="card-link">
    <div class="card">
        <h2>🟡 Pending</h2>
        <h1><?php echo $pending_requests['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=In+Progress" class="card-link">
    <div class="card">
        <h2>🔵 In Progress</h2>
        <h1><?php echo $progress_requests['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=Completed" class="card-link">
    <div class="card">
        <h2>🟢 Completed</h2>
        <h1><?php echo $completed_requests['total']; ?></h1>
    </div>
</a>

</div>

</div>

<?php
include("../includes/footer.php");
?>