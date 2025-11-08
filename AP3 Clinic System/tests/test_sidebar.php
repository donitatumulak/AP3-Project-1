<?php
// Start session for testing
session_start();

// Test different user types - CHANGE THESE TO TEST DIFFERENT USERS
$_SESSION['user_type'] = 'superadmin'; // Try: superadmin, doctor, staff, patient
$_SESSION['user_name'] = 'testuser';
$_SESSION['full_name'] = 'John Doe';

// Test different pages - CHANGE THIS TO TEST ACTIVE STATES
$page = 'pages/user_management'; // Try: dashboards/dashboard, pages/appointment_management, etc.

$base_path = '/AP3 Clinic System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Test - <?php echo $_SESSION['user_type']; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Your CSS Files -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/stylesheet.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/sidebar.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/management_styles.css">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .test-container {
            min-height: 100vh;
        }
        .content-area {
            padding: 20px;
            background: white;
            margin-left: 250px; /* Match sidebar width */
        }
        .test-controls {
            background: #e9ecef;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <!-- Sidebar -->
        <?php include '../includes/sidebar_user.php'; ?>
        
        <!-- Main Content -->
        <div class="content-area">
            <div class="test-controls">
                <h3>Sidebar Test Controls</h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Change User Type:</h5>
                        <a href="test_sidebar.php?user_type=superadmin&page=<?php echo $page; ?>" class="btn btn-primary btn-sm">Super Admin</a>
                        <a href="test_sidebar.php?user_type=doctor&page=<?php echo $page; ?>" class="btn btn-info btn-sm">Doctor</a>
                        <a href="test_sidebar.php?user_type=staff&page=<?php echo $page; ?>" class="btn btn-warning btn-sm">Staff</a>
                        <a href="test_sidebar.php?user_type=patient&page=<?php echo $page; ?>" class="btn btn-success btn-sm">Patient</a>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Change Active Page:</h5>
                        <a href="test_sidebar.php?user_type=<?php echo $_SESSION['user_type']; ?>&page=dashboards/dashboard" class="btn btn-outline-primary btn-sm">Dashboard</a>
                        <a href="test_sidebar.php?user_type=<?php echo $_SESSION['user_type']; ?>&page=pages/user_management" class="btn btn-outline-primary btn-sm">User Management</a>
                        <a href="test_sidebar.php?user_type=<?php echo $_SESSION['user_type']; ?>&page=pages/appointment_management" class="btn btn-outline-primary btn-sm">Appointments</a>
                        <a href="test_sidebar.php?user_type=<?php echo $_SESSION['user_type']; ?>&page=pages/payment_management" class="btn btn-outline-primary btn-sm">Payments</a>
                    </div>
                </div>
                
                <div class="mt-3">
                    <strong>Current Settings:</strong><br>
                    User Type: <?php echo $_SESSION['user_type']; ?><br>
                    Active Page: <?php echo $page; ?><br>
                    Full Name: <?php echo $_SESSION['full_name']; ?>
                </div>
            </div>
            
            <h2>Main Content Area</h2>
            <p>This simulates the main content area of your application.</p>
            <p>The sidebar should appear on the left with the appropriate menu items based on the user type.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('show');
        
        // Adjust content margin when sidebar is toggled
        const content = document.querySelector('.content-area');
        if (sidebar.classList.contains('show')) {
            content.style.marginLeft = '250px';
        } else {
            content.style.marginLeft = '0';
        }
    }
    </script>
</body>
</html>