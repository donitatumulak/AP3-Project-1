<?php
// auth_check.php - Include this at the top of all dashboard pages
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Optional: Check specific role if needed
function requirePatient() {
    if ($_SESSION['user_type'] !== 'patient') {
        header("Location: ../unauthorized.php");
        exit();
    }
}

function requireDoctor() {
    if ($_SESSION['user_type'] !== 'doctor') {
        header("Location: ../unauthorized.php");
        exit();
    }
}

function requireStaff() {
    if ($_SESSION['user_type'] !== 'staff') {
        header("Location: ../unauthorized.php");
        exit();
    }
}

function requireAdmin() {
    if (!$_SESSION['user_is_superadmin']) {
        header("Location: ../unauthorized.php");
        exit();
    }
}
?>