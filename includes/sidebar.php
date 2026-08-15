<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/permissions.php");

include_once(__DIR__ . "/../config/db.php");

$role = $_SESSION['role'] ?? '';

/* Load Company Name */

$settings_result = mysqli_query(
    $conn,
    "SELECT company_name FROM settings WHERE id=1"
);

$settings = mysqli_fetch_assoc($settings_result);

$company_name = $settings['company_name'] ?? 'G3 Systems';

?>

<div class="sidebar">

    <div class="sidebar-logo">

        <img src="../assets/images/logo.png" alt="G3 Logo" class="logo">

       <h2><?php echo htmlspecialchars($company_name); ?></h2>

        <p>Service Management System</p>

    </div>

    <h4 class="menu-title">MENU</h4>

    <ul>

    <!-- Everyone -->
    <li>
        <a href="../dashboard/index.php">🏠 Dashboard</a>
    </li>


    <!-- Manager + Reception -->
    <?php if (in_array($role, ['Manager', 'Reception'])): ?>

        <li>
            <a href="../customers/view_customers.php">👥 Customers</a>
        </li>

    <?php endif; ?>


    <!-- Manager + Reception + Technician -->
    <?php if (in_array($role, ['Manager', 'Reception', 'Technician'])): ?>

        <li>
            <a href="../machines/view_machines.php">🖨️ Machines</a>
        </li>

        <li>
            <a href="../service_requests/view_requests.php">
                🛠️ Service Requests
            </a>
        </li>

    <?php endif; ?>


    <!-- Manager + Technician -->
    <?php if (in_array($role, ['Manager', 'Technician'])): ?>

        <li>
            <a href="../job_cards/view_job_cards.php">📋 Job Cards</a>
        </li>

    <?php endif; ?>


    <!-- Manager only -->
<?php if ($role === 'Manager'): ?>

    <li>
        <a href="../technicians/view_technicians.php">
            👨‍🔧 Technicians
        </a>
    </li>

    <li>
        <a href="../reports/index.php">
            📊 Reports
        </a>
    </li>
    
    <li>
    <a href="../users/index.php">
    👥 Users
    </a>
    </li>

    <li>
        <a href="../settings/index.php">
            ⚙️ Settings
        </a>
    </li>

<?php endif; ?>


    <!-- Everyone -->
    <li>
        <a href="../auth/logout.php">🚪 Logout</a>
    </li>

</ul>

</div>