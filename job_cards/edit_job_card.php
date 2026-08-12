<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_job_cards.php");
    exit();
}


/* ==============================
   Technician Security
   ============================== */

if ($_SESSION['role'] === 'Technician') {

    $user_id = intval($_SESSION['user_id']);

    $security_stmt = mysqli_prepare(
        $conn,
        "SELECT job_cards.id
         FROM job_cards
         INNER JOIN technicians
             ON job_cards.technician_id = technicians.id
         WHERE job_cards.id = ?
         AND technicians.user_id = ?"
    );

    mysqli_stmt_bind_param(
        $security_stmt,
        "ii",
        $id,
        $user_id
    );

    mysqli_stmt_execute($security_stmt);

    $security_result = mysqli_stmt_get_result($security_stmt);

    if (mysqli_num_rows($security_result) === 0) {

        header("Location: view_job_cards.php");
        exit();

    }
}


/* ==============================
   Get Job Card
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM job_cards WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    header("Location: view_job_cards.php");
    exit();

}

$row = mysqli_fetch_assoc($result);
?>

<div class="main-content">

<h1>Edit Job Card</h1>

<form action="update_job_card.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<label>Job Card Number</label>

<input type="text"
value="<?php echo htmlspecialchars($row['job_card_number']); ?>"
readonly>

<br><br>

<label>Work Done</label>

<textarea
name="work_done"
rows="6"><?php echo htmlspecialchars($row['work_done']); ?></textarea>

<br><br>

<label>Remarks</label>

<textarea
name="remarks"
rows="5"><?php echo htmlspecialchars($row['remarks']); ?></textarea>

<br><br>

<label>Status</label>

<select name="status">

<option value="Open"
<?php if($row['status']=="Open") echo "selected"; ?>>
Open
</option>

<option value="In Progress"
<?php if($row['status']=="In Progress") echo "selected"; ?>>
In Progress
</option>

<option value="Completed"
<?php if($row['status']=="Completed") echo "selected"; ?>>
Completed
</option>

</select>

<br><br>

<button class="btn btn-add">
Save Changes
</button>

</form>

</div>

<?php include("../includes/footer.php"); ?>