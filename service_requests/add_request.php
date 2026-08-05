<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");

// Load Customers
$customers = mysqli_query($conn, "SELECT id, customer_name FROM customers ORDER BY customer_name ASC");

// Load Technicians
$technicians = mysqli_query($conn, "SELECT id, full_name FROM technicians WHERE status <> 'On Leave' ORDER BY full_name ASC");
?>

<div class="main-content">

<h1>Add Service Request</h1>

<form action="save_request.php" method="POST">

    <label>Customer</label>

    <select name="customer_id" id="customer" required>

        <option value="">-- Select Customer --</option>

        <?php while($customer = mysqli_fetch_assoc($customers)){ ?>

            <option value="<?php echo $customer['id']; ?>">
                <?php echo $customer['customer_name']; ?>
            </option>

        <?php } ?>

    </select>

    <br><br>

    <label>Machine</label>

    <select name="machine_id" id="machine" required>

        <option value="">-- Select Customer First --</option>

    </select>

    <br><br>

    <label>Issue Description</label>

    <textarea
        name="issue_description"
        rows="5"
        required
        placeholder="Describe the fault..."></textarea>

    <br><br>

    <label>Priority</label>

    <select name="priority">

        <option value="Low">Low</option>

        <option value="Medium" selected>Medium</option>

        <option value="High">High</option>

    </select>

    <br><br>

    <label>Assign Technician</label>

    <select name="technician_id" required>

        <option value="">-- Select Technician --</option>

        <?php while($tech = mysqli_fetch_assoc($technicians)){ ?>

            <option value="<?php echo $tech['id']; ?>">

                <?php echo $tech['full_name']; ?>

            </option>

        <?php } ?>

    </select>

    <br><br>

    <label>Status</label>

    <select name="status">

        <option value="Pending" selected>Pending</option>

        <option value="In Progress">In Progress</option>

        <option value="Completed">Completed</option>

    </select>

    <br><br>

    <button type="submit" class="btn btn-add">
        Save Service Request
    </button>

</form>

</div>

<script>

document.getElementById("customer").addEventListener("change", function(){

    var customer_id = this.value;

    var xhr = new XMLHttpRequest();

    xhr.open("GET","get_machines.php?customer_id="+customer_id,true);

    xhr.onload = function(){

        document.getElementById("machine").innerHTML = this.responseText;

    };

    xhr.send();

});

</script>

<?php
include("../includes/footer.php");
?>