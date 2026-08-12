<?php

function requireRole($roles)
{
    if (!isset($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    if (!in_array($_SESSION['role'], $roles)) {

        echo "
        <div style='padding:30px;text-align:center;font-family:Arial;'>
            <h2>🚫 Access Denied</h2>
            <p>You do not have permission to access this page.</p>
            <a href='../dashboard/index.php'>Return to Dashboard</a>
        </div>";

        exit();
    }
}