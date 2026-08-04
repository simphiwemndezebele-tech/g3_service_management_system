<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="main-content">

<h1>Add Technician</h1>

<form action="save_technician.php" method="POST">

    <label>Full Name</label>
    <input type="text" name="full_name" required>

    <label>Phone Number</label>
    <input type="text" name="phone">

    <label>Email Address</label>
    <input type="email" name="email">

    <label>Specialization</label>
    <input type="text" name="specialization"
           placeholder="e.g. Olivetti Printers">

    <label>Status</label>

    <select name="status">

        <option value="Available">Available</option>

        <option value="Busy">Busy</option>

        <option value="On Leave">On Leave</option>

    </select>

    <br><br>

    <button type="submit" class="btn btn-add">
        Save Technician
    </button>

</form>

</div>

<?php
include("../includes/footer.php");
?>