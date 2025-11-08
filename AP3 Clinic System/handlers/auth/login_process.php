<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/users/User.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DEBUG: Show that we reached the handler
//echo "DEBUG: Reached login handler<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->connect();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        $user = new User($db);
        
        // Get and sanitize form data
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Store username for form persistence
        $_SESSION['form_data']['username'] = $username;
        
        // Basic validation
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Please enter both username and password";
            header("Location: ../../login.php");
            exit();
        }
        
        // Attempt login using your User class
        $result = $user->login($username, $password);
        
            if ($result['status'] === 'success') {
        // Login successful - store user data in session
        $userData = $result['data'];
        
        $_SESSION['user'] = $userData;
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['user_name'] = $userData['user_name'];
        $_SESSION['user_type'] = $userData['user_type'];
        $_SESSION['user_is_superadmin'] = $userData['user_is_superadmin'];

          // Add these lines to store role-specific IDs
            if (!empty($userData['pat_id'])) {
                $_SESSION['pat_id'] = $userData['pat_id'];
                $_SESSION['profile_id'] = $userData['pat_id'];
            } elseif (!empty($userData['doc_id'])) {
                $_SESSION['doc_id'] = $userData['doc_id'];
                $_SESSION['profile_id'] = $userData['doc_id'];
            } elseif (!empty($userData['staff_id'])) {
                $_SESSION['staff_id'] = $userData['staff_id'];
                $_SESSION['profile_id'] = $userData['staff_id'];
            } else {
                // For superadmin / general user
                $_SESSION['profile_id'] = $userData['user_id'];
            }
        
        // Fetch complete name based on user type
        $full_name = $userData['user_name']; // fallback to username
        
        try {
            if (!empty($userData['pat_id'])) {
                $_SESSION['profile_id'] = $userData['pat_id'];
                // Query patient table for complete name
                $query = "SELECT pat_first_name, pat_last_name FROM patient WHERE pat_id = :profile_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':profile_id', $userData['pat_id']);
                $stmt->execute();
                if ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $full_name = $patient['pat_first_name'] . ' ' . $patient['pat_last_name'];
                }
                
            } elseif (!empty($userData['doc_id'])) {
                $_SESSION['profile_id'] = $userData['doc_id'];
                // Query doctor table for complete name
                $query = "SELECT doc_first_name, doc_last_name FROM doctor WHERE doc_id = :profile_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':profile_id', $userData['doc_id']);
                $stmt->execute();
                if ($doctor = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $full_name = "Dr. " . $doctor['doc_first_name'] . ' ' . $doctor['doc_last_name'];
                }
                
            } elseif (!empty($userData['staff_id'])) {
                $_SESSION['profile_id'] = $userData['staff_id'];
                // Query staff table for complete name
                $query = "SELECT staff_first_name, staff_last_name FROM staff WHERE staff_id = :profile_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':profile_id', $userData['staff_id']);
                $stmt->execute();
                if ($staff = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $full_name = $staff['staff_first_name'] . ' ' . $staff['staff_last_name'];
                }
                
            } else {
            // FOR ADMINS/SUPERADMINS
            $_SESSION['profile_id'] = $userData['user_id'];
            
            $username = $userData['user_name'];
            
            // Convert firstname.lastname to proper name format
            if (strpos($username, '.') !== false) {
                $name_parts = explode('.', $username);
                $first_name = ucfirst($name_parts[0]);
                $last_name = ucfirst($name_parts[1] ?? '');
                $full_name = trim($first_name . ' ' . $last_name);
            } else {
                // Fallback for non-standard usernames
                $full_name = ucwords(str_replace(['_', '-'], ' ', $username));
            }
        }
        } catch (Exception $e) {
            // If query fails, fall back to basic username
            error_log("Error fetching user details: " . $e->getMessage());
        }
        
        // Store the complete name in session
        $_SESSION['full_name'] = $full_name;
                
            // Clear form data
            unset($_SESSION['form_data']);
            
            // Redirect to appropriate dashboard
            switch ($_SESSION['user_type']) {
                case 'patient':
                    header("Location: ../../dashboards/patient_dashboard.php");
                    break;
                case 'doctor':
                    header("Location: ../../dashboards/doctor_dashboard.php");
                    break;
                case 'staff':
                    header("Location: ../../dashboards/staff_dashboard.php");
                    break;
                case 'superadmin':
                    header("Location: ../../dashboards/superadmin_dashboard.php");
                    break;
                default:
                    header("Location: ../../dashboards/dashboard.php");
            }
            exit();
            
        } else {
            // Login failed
            $_SESSION['error'] = $result['message'] ?: "Invalid username or password";
            header("Location: ../../login.php");
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "System error: " . $e->getMessage();
        header("Location: ../../login.php");
        exit();
    }
} else {
    // Not a POST request - redirect to login
    header("Location: ../../login.php");
    exit();
}
?>