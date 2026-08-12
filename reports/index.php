<?php
session_start();
require_once("../includes/permissions.php");
requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");
?>

<div class="main-content">

    <h1>Reports Center</h1>

    <p>Select a report to view.</p>

    <div class="dashboard-cards">

        <div class="card">
            <h2>👥 Customer Report</h2>
            <br>
            <a href="customer_report.php" class="btn btn-add">
                Open Report
            </a>
        </div>

        <div class="card">
            <h2>🖨 Machine Report</h2>
            <br>
            <a href="machine_report.php" class="btn btn-add">
                Open Report
            </a>
        </div>

        <div class="card">
            <h2>👨‍🔧 Technician Report</h2>
            <br>
            <a href="technician_report.php" class="btn btn-add">
                Open Report
            </a>
        </div>

        <div class="card">
            <h2>🛠 Service Request Report</h2>
            <br>
            <a href="service_report.php" class="btn btn-add">
                Open Report
            </a>
        </div>

        <div class="card">
            <h2>📋 Job Card Report</h2>
            <br>
            <a href="job_card_report.php" class="btn btn-add">
                Open Report
            </a>
        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>