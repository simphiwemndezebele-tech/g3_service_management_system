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
?> 
 
<div class="main-content"> 
 
<h1>➕ Add User</h1> 
 
<p>Create a new system user and assign a role.</p> 
 
<br> 
 
<form action="save_user.php" method="POST"> 
 
    <label>Username</label> 
 
    <input 
        type="text" 
        name="username" 
        placeholder="Enter username" 
        required 
    > 
 
    <br><br> 
 
 
    <label>Full Name</label> 
 
    <input 
        type="text" 
        name="full_name" 
        placeholder="Enter full name" 
        required 
    > 
 
    <br><br> 
 
 
    <label>Email</label> 
 
    <input 
        type="email" 
        name="email" 
        placeholder="Enter email address" 
        required 
    > 
 
    <br><br> 
 
 
    <label>Password</label> 
 
    <input 
        type="password" 
        name="password" 
        placeholder="Enter password" 
        required 
    > 
 
    <br><br> 
 
 
    <label>Role</label> 
 
    <select name="role" required> 
 
        <option value="">-- Select Role --</option> 
 
        <option value="Manager"> 
            Manager 
        </option> 
 
        <option value="Technician"> 
            Technician 
        </option> 
 
        <option value="Reception"> 
            Reception 
        </option> 
 
    </select> 
 
    <br><br> 
 
 
    <button 
        type="submit" 
        name="save_user" 
        class="btn btn-add"> 
 
        💾 Save User 
 
    </button> 
 
 
    <a 
        href="index.php" 
        class="btn btn-search"> 
 
        ↩️ Cancel 
 
    </a> 
 
</form> 
 
</div> 
 
<?php include("../includes/footer.php"); ?>