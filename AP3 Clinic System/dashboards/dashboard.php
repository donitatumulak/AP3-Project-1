<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Redirect based on user type
switch ($_SESSION['user_type']) {
    case 'patient':
        header("Location: patient_dashboard.php");
        break;
    case 'doctor':
        header("Location: doctor_dashboard.php");
        break;
    case 'staff':
        header("Location: staff_dashboard.php");
        break;
    case 'superadmin':
        header("Location: superadmin_dashboard.php");
        break;
    default:
        // Logout if user type is invalid
        session_destroy();
        header("Location: ../login.php");
}
exit();
?>