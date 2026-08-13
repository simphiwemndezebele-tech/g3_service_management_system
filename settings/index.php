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


/* ==============================
   Load Company Settings
   ============================== */

$result = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");

$settings = mysqli_fetch_assoc($result);


/* ==============================
   Default Values
   ============================== */

if (!$settings) {

    $company_name = "G3 Systems";
    $company_phone = "";
    $company_email = "";
    $company_address = "";

} else {

    $company_name = $settings['company_name'];
    $company_phone = $settings['company_phone'];
    $company_email = $settings['company_email'];
    $company_address = $settings['company_address'];

}

?>

<div class="main-content">

    <h1>⚙️ System Settings</h1>

    <p>Manage your company information and system settings.</p>

    <form action="save_settings.php" method="POST" enctype="multipart/form-data">

        <label>Company Name</label>

        <input
            type="text"
            name="company_name"
            value="<?php echo htmlspecialchars($company_name); ?>"
            required
        >

        <br><br>


        <label>Company Phone</label>

        <input
            type="text"
            name="company_phone"
            value="<?php echo htmlspecialchars($company_phone); ?>"
        >

        <br><br>


        <label>Company Email</label>

        <input
            type="email"
            name="company_email"
            value="<?php echo htmlspecialchars($company_email); ?>"
        >

        <br><br>


        <label>Company Address</label>

<textarea
    name="company_address"
    rows="4"
><?php echo htmlspecialchars($company_address); ?></textarea>

<br><br>

<label>Company Logo</label>

<br><br>

<?php
$logo_path = "../assets/images/logo.png";
?>

<img
    src="<?php echo $logo_path; ?>"
    alt="Company Logo"
    style="max-width:180px; max-height:100px; display:block; margin-bottom:15px;"
>

<input
    type="file"
    name="company_logo"
    accept="image/png, image/jpeg, image/jpg"
>

<br><br>

<button type="submit" class="btn btn-add">
    💾 Save Settings
</button>

    </form>

</div>

<?php

include("../includes/footer.php");

?>