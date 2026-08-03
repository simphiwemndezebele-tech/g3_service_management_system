<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<?php
include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="main-content">

    <h1>Dashboard</h1>

    <p>Welcome to G3 Service Management System Version 2.0</p>

    <div class="dashboard-cards">

        <div class="card">
            <h2>Customers</h2>
            <h1>0</h1>
        </div>

        <div class="card">
            <h2>Machines</h2>
            <h1>0</h1>
        </div>

        <div class="card">
            <h2>Pending Jobs</h2>
            <h1>0</h1>
        </div>

        <div class="card">
            <h2>Completed Jobs</h2>
            <h1>0</h1>
        </div>

    


    </div>

</div>

<?php
include("../includes/footer.php");
?>