<?php

function hasRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function isManager()
{
    return hasRole('Manager');
}

function isTechnician()
{
    return hasRole('Technician');
}

function isReception()
{
    return hasRole('Reception');
}

function requireRole($allowedRoles)
{
    if (!isset($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header("Location: ../dashboard/index.php");
        exit();
    }
}
?>