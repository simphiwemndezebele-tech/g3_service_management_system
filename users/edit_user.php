<?php
session_start();

require_once("../includes/permissions.php");

requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Get User ID
================================ */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: index.php");
    exit();

}

$user_id = intval($_GET['id']);


/* ==============================
   Get User
================================ */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, username, full_name, role
     FROM users
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


/* ==============================
   User Not Found
================================ */

if (!$user) {

    die("User not found.");

}


/* ==============================
   Page Layout
================================ */

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="main-content">

<h1>✏️ Edit User</h1>

<p>Update the user's account information.</p>

<br>

<form action="update_user.php" method="POST">

    <!-- User ID -->

    <input
        type="hidden"
        name="id"
        value="<?php echo $user['id']; ?>"
    >


    <!-- Username -->

    <label>Username</label>

    <input
        type="text"
        name="username"
        value="<?php echo htmlspecialchars($user['username']); ?>"
        required
    >

    <br><br>


    <!-- Full Name -->

    <label>Full Name</label>

    <input
        type="text"
        name="full_name"
        value="<?php echo htmlspecialchars($user['full_name']); ?>"
        required
    >

    <br><br>


    <!-- Password -->

    <label>New Password</label>

    <input
        type="password"
        name="password"
        placeholder="Leave blank to keep current password"
    >

    <br>

    <small>
        Leave this field blank if you do not want to change the password.
    </small>

    <br><br>


    <!-- Role -->

    <label>Role</label>

    <select name="role" required>

        <option
            value="Manager"
            <?php
            if ($user['role'] === 'Manager') {
                echo 'selected';
            }
            ?>
        >
            Manager
        </option>

        <option
            value="Technician"
            <?php
            if ($user['role'] === 'Technician') {
                echo 'selected';
            }
            ?>
        >
            Technician
        </option>

        <option
            value="Reception"
            <?php
            if ($user['role'] === 'Reception') {
                echo 'selected';
            }
            ?>
        >
            Reception
        </option>

    </select>

    <br><br>


    <!-- Buttons -->

    <button
        type="submit"
        name="update_user"
        class="btn btn-add"
    >

        💾 Update User

    </button>


    <a
        href="index.php"
        class="btn btn-search"
    >

        ↩️ Cancel

    </a>

</form>

</div>

<?php include("../includes/footer.php"); ?>