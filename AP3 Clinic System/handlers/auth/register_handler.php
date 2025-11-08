<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/users/Patient.php';
require_once '../../classes/users/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->connect();
        
        // Start transaction to ensure both operations succeed or fail together
        $db->beginTransaction();

        // Step 1: Create Patient Record
        $patient = new Patient($db);
        $patientResult = $patient->addPatient(
            $_POST['fname'],      // pat_first_name
            $_POST['lname'],      // pat_last_name  
            $_POST['mname'] ?? '', // pat_middle_init
            $_POST['dob'],        // pat_dob
            $_POST['gender'],     // pat_gender
            $_POST['contact'],    // pat_contact_num
            $_POST['email'],      // pat_email
            $_POST['address']     // pat_address
        );

        if ($patientResult['status'] !== 'success') {
            throw new Exception("Patient creation failed: " . $patientResult['message']);
        }

        $pat_id = $patientResult['data']['pat_id'];

        // Step 2: Create User Account linked to the Patient
        $user = new User($db);
        $userResult = $user->createUser(
            $_POST['username'],    // user_name
            $_POST['password'],    // user_password
            $pat_id,               // pat_id (links user to patient)
            null,                  // doc_id (null for patients)
            null,                  // staff_id (null for patients)
            false                  // is_superadmin (false for patients)
        );

        if ($userResult['status'] !== 'success') {
            throw new Exception("Account creation failed: " . $userResult['message']);
        }

        // If both operations successful, commit transaction
        $db->commit();

        // Redirect to success page
        $_SESSION['success_message'] = "Account created successfully! You can now login with your username: " . htmlspecialchars($_POST['username']);
        header('Location: ../../login.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction if any operation fails
        if (isset($db)) {
            $db->rollBack();
        }
        
        // Store error message and redirect back to registration form
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: ../../registration.php');
        exit;
    }
} else {
    // If not POST request, redirect to registration page
    header('Location: ../../registration.php');
    exit;
}
?>