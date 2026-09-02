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

$id = $_GET['id'];
// Technician security check
if ($_SESSION['role'] === 'Technician') {

    $user_id = $_SESSION['user_id'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT sr.id
         FROM service_requests sr
         INNER JOIN technicians t
         ON sr.technician_id = t.id
         WHERE sr.id = ?
         AND t.user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $security_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($security_result) === 0) {

        header("Location: view_requests.php");
        exit();

    }
}

$sql = "SELECT * FROM service_requests WHERE id=?";
$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);
$request=mysqli_fetch_assoc($result);

$customers=mysqli_query($conn,"SELECT * FROM customers ORDER BY customer_name");
$machines=mysqli_query($conn,"SELECT * FROM machines WHERE customer_id=".$request['customer_id']." ORDER BY asset_number");
$technicians=mysqli_query($conn,"SELECT * FROM technicians ORDER BY full_name");
?>

<div class="main-content">

<h1>Edit Service Request</h1>

<form action="update_request.php" method="POST">

<input type="hidden" name="id" value="<?php echo $request['id']; ?>">

<label>Customer</label>

<select name="customer_id" required>

<?php while($c=mysqli_fetch_assoc($customers)){ ?>

<option value="<?php echo $c['id']; ?>"
<?php if($request['customer_id']==$c['id']) echo "selected"; ?>>

<?php echo $c['customer_name']; ?>

</option>

<?php } ?>

</select>

<br><br>

<label>Machine</label>

<select name="machine_id" required>

<?php while($m=mysqli_fetch_assoc($machines)){ ?>

<option value="<?php echo $m['id']; ?>"
<?php if($request['machine_id']==$m['id']) echo "selected"; ?>>

<?php echo $m['asset_number']." - ".$m['machine_model']; ?>

</option>

<?php } ?>

</select>

<br><br>

<label>Issue Description</label>

<textarea name="issue_description" rows="5" required><?php echo $request['issue_description']; ?></textarea>

<br><br>

<label>Priority</label>

<select name="priority">

<option value="Low" <?php if($request['priority']=="Low") echo "selected"; ?>>Low</option>

<option value="Medium" <?php if($request['priority']=="Medium") echo "selected"; ?>>Medium</option>

<option value="High" <?php if($request['priority']=="High") echo "selected"; ?>>High</option>

</select>

<br><br>

<label>Technician</label>

<?php if ($_SESSION['role'] === 'Technician') { ?>

    <!-- Technician cannot change the assigned technician -->

    <?php
    $assigned_technician = mysqli_query(
        $conn,
        "SELECT full_name
         FROM technicians
         WHERE id=" . intval($request['technician_id'])
    );

    $assigned = mysqli_fetch_assoc($assigned_technician);
    ?>

    <input
        type="text"
        value="<?php echo htmlspecialchars($assigned['full_name']); ?>"
        readonly
    >

    <!-- Keep the technician ID so the request remains assigned correctly -->
    <input
        type="hidden"
        name="technician_id"
        value="<?php echo intval($request['technician_id']); ?>"
    >

<?php } else { ?>

    <!-- Manager and Reception can reassign technicians -->

    <select name="technician_id">

        <?php while($t=mysqli_fetch_assoc($technicians)){ ?>

            <option
                value="<?php echo $t['id']; ?>"
                <?php if($request['technician_id']==$t['id']) echo "selected"; ?>
            >

                <?php echo htmlspecialchars($t['full_name']); ?>

            </option>

        <?php } ?>

    </select>

<?php } ?>

<br><br>

<label>Status</label>

<?php

/* ==================================================
   CHECK IF JOB CARD EXISTS
================================================== */

$job_card_check = mysqli_prepare(
    $conn,
    "SELECT id, status
     FROM job_cards
     WHERE service_request_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $job_card_check,
    "i",
    $request['id']
);

mysqli_stmt_execute($job_card_check);

$job_card_result =
    mysqli_stmt_get_result($job_card_check);

$linked_job_card =
    mysqli_fetch_assoc($job_card_result);

?>


<?php if ($linked_job_card): ?>

    <!-- ==========================================
         JOB CARD EXISTS
         STATUS CONTROLLED BY JOB CARD
    =========================================== -->

    <?php

    if ($linked_job_card['status'] === 'Open') {

        $display_status = 'Pending';

    } elseif ($linked_job_card['status'] === 'In Progress') {

        $display_status = 'In Progress';

    } else {

        $display_status = 'Completed';

    }

    ?>

    <input
        type="text"
        value="<?php echo htmlspecialchars($display_status); ?>"
        readonly
    >

    <!-- Keep status for update_request.php -->

    <input
        type="hidden"
        name="status"
        value="<?php echo htmlspecialchars($display_status); ?>"
    >

    <small style="color:#777;">

        🔒 Status is controlled by the linked Job Card.

    </small>


<?php else: ?>

    <!-- ==========================================
         NO JOB CARD
         NORMAL STATUS CONTROL
    =========================================== -->

    <select name="status">

        <option
            value="Pending"
            <?php
            if ($request['status'] == "Pending")
                echo "selected";
            ?>
        >
            Pending
        </option>


        <option
            value="In Progress"
            <?php
            if ($request['status'] == "In Progress")
                echo "selected";
            ?>
        >
            In Progress
        </option>


        <option
            value="Completed"
            <?php
            if ($request['status'] == "Completed")
                echo "selected";
            ?>
        >
            Completed
        </option>

    </select>

<?php endif; ?>

<br><br>

<button class="btn btn-edit">Update Request</button>

<a href="view_requests.php" class="btn btn-delete">Cancel</a>

</form>

</div>

<?php include("../includes/footer.php"); ?>