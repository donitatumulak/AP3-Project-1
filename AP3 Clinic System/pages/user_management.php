<?php
session_start();

//For demo purposes - allow access without login, but in production use:
if (!isset($_SESSION['user_id']) || !$_SESSION['user_is_superadmin']) {
     header("Location: login.php");
     exit();
 }

require_once '../config/Database.php';
$database = new Database();
$db = $database->connect();

require_once '../classes/users/Doctor.php';
require_once '../classes/users/Patient.php';
require_once '../classes/users/Staff.php';
require_once '../classes/users/User.php';

// Initialize classes
$doctor = new Doctor($db);
$patient = new Patient($db);
$staff = new Staff($db);
$user = new User($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        // Doctor actions
        case 'add_doctor':
            $result = $doctor->addDoctor(
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['middle_init'] ?? '',
                $_POST['contact_num'],
                $_POST['email'],
                $_POST['spec_id']
            );

            // ✅ Redirect to user creation after success
            if ($result['status'] === 'success') {
                $new_doc_id = $result['data']['doc_id']; // Fixed: access via data array
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Doctor added successfully! Now create their user account.'
                ];
                
              header("Location:/AP3%20Clinic%20System/pages/user_management.php?open_modal=user_account_form&id={$new_doc_id}&role=doctor");
                exit();
            }
            break;
            
        case 'update_doctor':
            $result = $doctor->updateDoctor(
                $_POST['doc_id'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['middle_init'] ?? '',
                $_POST['contact_num'],
                $_POST['email'],
                $_POST['spec_id']
            );
            break;
            
        case 'delete_doctor':
            $result = $doctor->deleteDoctor($_POST['doc_id']);
            break;
            
        // Patient actions
        case 'add_patient':
            $result = $patient->addPatient(
                $_POST['pat_first_name'],
                $_POST['pat_last_name'],
                $_POST['pat_middle_init'] ?? '',
                $_POST['pat_dob'],
                $_POST['pat_gender'],
                $_POST['pat_contact_num'],
                $_POST['pat_email'],
                $_POST['pat_address']
            );

            // ✅ Redirect to user creation after success
            if ($result['status'] === 'success') {
                $new_pat_id = $result['data']['pat_id']; // Fixed: access via data array
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Patient added successfully! Now create their user account.'
                ];
              header("Location:/AP3%20Clinic%20System/pages/user_management.php?open_modal=user_account_form&id={$new_pat_id}&role=patient");
                exit();
            }
            break;
            
        case 'update_patient':
            $result = $patient->updatePatient(
                $_POST['pat_id'],
                $_POST['pat_first_name'],
                $_POST['pat_last_name'],
                $_POST['pat_middle_init'] ?? '',
                $_POST['pat_dob'],
                $_POST['pat_gender'],
                $_POST['pat_contact_num'],
                $_POST['pat_email'],
                $_POST['pat_address']
            );
            // ❌ Removed incorrect redirect from update_patient
            break;
            
        case 'delete_patient':
            $result = $patient->deletePatient($_POST['pat_id']);
            break;
            
        // Staff actions
        case 'add_staff':
            $result = $staff->addStaff(
                $_POST['staff_first_name'],
                $_POST['staff_last_name'],
                $_POST['staff_middle_init'] ?? '',
                $_POST['staff_contact_num'],
                $_POST['staff_email']
            );

            // ✅ Added missing redirect for staff creation
            if ($result['status'] === 'success') {
                $new_staff_id = $result['data']['staff_id']; // Fixed: access via data array
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Staff added successfully! Now create their user account.'
                ];
              header("Location:/AP3%20Clinic%20System/pages/user_management.php?open_modal=user_account_form&id={$new_staff_id}&role=staff");
                exit();
            }
            break;
            
        case 'update_staff':
            $result = $staff->updateStaff(
                $_POST['staff_id'],
                $_POST['staff_first_name'],
                $_POST['staff_last_name'],
                $_POST['staff_middle_init'] ?? '',
                $_POST['staff_contact_num'],
                $_POST['staff_email']
            );
            break;
            
        case 'delete_staff':
            $result = $staff->deleteStaff($_POST['staff_id']);
            break;
            
        // User account actions
        case 'add_user':
            $result = $user->createUser(
                $_POST['user_name'],
                $_POST['user_password'],
                $_POST['pat_id'] ?? null,
                $_POST['doc_id'] ?? null,
                $_POST['staff_id'] ?? null,
                $_POST['is_superadmin'] ?? false
            );
            break;
    }
    
    // Only set session message and redirect if not already handled
    if (isset($result) && !($result['status'] === 'success' && in_array($action, ['add_doctor', 'add_patient', 'add_staff']))) {
        $_SESSION['message'] = [
            'type' => $result['status'],
            'text' => $result['message']
        ];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}



// Get all data for display
$doctors = $doctor->getAllDoctors();
$patients = $patient->getAllPatient();
$staff_members = $staff->getAllStaff();
$users = $user->getAllUsers();

// If redirected here after creating a new record
if (isset($_GET['open_modal']) && $_GET['open_modal'] === 'user_account_form') {
    $role = $_GET['role'] ?? '';
    $id = $_GET['id'] ?? '';

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            openAddUserModal('{$role}', '{$id}');
        });
    </script>";
}

$page = 'pages/user_management';
include '../includes/header.php';
?>

<body class="management-page">
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar_user.php'; ?>
            
            <!-- Main Content -->
            <div class="col-lg-10 management-content">
                <!-- Welcome Header -->
                <div class="management-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1><i class="fas fa-users-cog me-3"></i>User Management System</h1>
                            <p class="text-muted mb-0">Manage doctors, patients, staff members, and user accounts</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card">
                                <div class="stats-content">
                                    <i class="fas fa-users stats-icon"></i>
                                    <div class="stats-text">
                                        <div class="stats-number">
                                            <?php 
                                            $total = 0;
                                            if ($doctors['status'] === 'success') $total += count($doctors['data']);
                                            if ($patients['status'] === 'success') $total += count($patients['data']);
                                            if ($staff_members['status'] === 'success') $total += count($staff_members['data']);
                                            if ($users['status'] === 'success') $total += count($users['data']);
                                            echo $total;
                                            ?>
                                        </div>
                                        <div class="stats-label">Total Records</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message']['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                        <?php echo $_SESSION['message']['text']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- User Management Tabs -->
                <div class="management-tabs">
                    <nav>
                        <div class="nav nav-tabs" id="userTabs" role="tablist">
                            <button class="nav-link active" id="doctors-tab" data-bs-toggle="tab" 
                                    data-bs-target="#doctors" type="button" role="tab">
                                <i class="fas fa-user-md"></i>
                                Doctors
                                <span class="badge bg-primary ms-1">
                                    <?php echo $doctors['status'] === 'success' ? count($doctors['data']) : 0; ?>
                                </span>
                            </button>

                            <button class="nav-link" id="patients-tab" data-bs-toggle="tab" 
                                    data-bs-target="#patients" type="button" role="tab">
                                <i class="fas fa-user-injured"></i>
                                Patients
                                <span class="badge bg-success ms-1">
                                    <?php echo $patients['status'] === 'success' ? count($patients['data']) : 0; ?>
                                </span>
                            </button>

                            <button class="nav-link" id="staff-tab" data-bs-toggle="tab" 
                                    data-bs-target="#staff" type="button" role="tab">
                                <i class="fas fa-user-tie"></i>
                                Staff
                                <span class="badge bg-info ms-1">
                                    <?php echo $staff_members['status'] === 'success' ? count($staff_members['data']) : 0; ?>
                                </span>
                            </button>

                            <button class="nav-link" id="users-tab" data-bs-toggle="tab" 
                                    data-bs-target="#users" type="button" role="tab">
                                <i class="fas fa-user-circle"></i>
                                User Accounts
                                <span class="badge bg-warning ms-1">
                                    <?php echo $users['status'] === 'success' ? count($users['data']) : 0; ?>
                                </span>
                            </button>
                        </div>
                    </nav>

                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white">
                        <!-- Doctors Tab -->
                        <div class="tab-pane fade show active" id="doctors" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search doctors..." 
                                                   id="search-doctors">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddDoctorModal()">
                                            <i class="fas fa-plus"></i> Add Doctor
                                        </button>
                                    </div>
                                </div>

                                <!-- Doctors Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover" id="doctors-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th>Doctor ID</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Email</th>
                                                <th>Specialization</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($doctors['status'] === 'success' && !empty($doctors['data'])): ?>
                                                <?php foreach ($doctors['data'] as $doc): ?>
                                                <tr>
                                                    <td><?php echo $doc['doc_id']; ?></td>
                                                    <td>
                                                        <strong>Dr. <?php echo htmlspecialchars($doc['doc_first_name'] . ' ' . $doc['doc_last_name']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($doc['doc_contact_num'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($doc['doc_email'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge pastel-blue"><?php echo htmlspecialchars($doc['spec_name'] ?? 'General'); ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-warning btn-action" 
                                                                    onclick="openEditDoctorModal(<?php echo $doc['doc_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-action"
                                                                    onclick="deleteDoctor(<?php echo $doc['doc_id']; ?>, 'Dr. <?php echo htmlspecialchars($doc['doc_first_name'] . ' ' . $doc['doc_last_name']); ?>')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-user-md fa-2x mb-2 d-block"></i>
                                                        <?php echo $doctors['status'] === 'error' ? 'Error: ' . htmlspecialchars($doctors['message']) : 'No doctors found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination for Doctors -->
                                <?php if ($doctors['status'] === 'success' && !empty($doctors['data']) && count($doctors['data']) > 10): ?>
                                <nav aria-label="Doctors pagination">
                                    <ul class="pagination justify-content-center mt-3" id="doctors-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Patients Tab -->
                        <div class="tab-pane fade" id="patients" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search patients..." 
                                                   id="search-patients">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddPatientModal()">
                                            <i class="fas fa-plus"></i> Add Patient
                                        </button>
                                    </div>
                                </div>

                                <!-- Patients Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover" id="patients-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th>Patient ID</th>
                                                <th>Name</th>
                                                <th>Date of Birth</th>
                                                <th>Gender</th>
                                                <th>Contact</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($patients['status'] === 'success' && !empty($patients['data'])): ?>
                                                <?php foreach ($patients['data'] as $pat): ?>
                                                <tr>
                                                    <td><?php echo $pat['pat_id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($pat['pat_first_name'] . ' ' . $pat['pat_last_name']); ?></strong>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($pat['pat_dob'])); ?></td>
                                                    <td>
                                                        <span class="badge pastel-green"><?php echo htmlspecialchars($pat['pat_gender']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($pat['pat_contact_num'] ?? 'N/A'); ?></td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-warning btn-action" 
                                                                    onclick="openEditPatientModal(<?php echo $pat['pat_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-action"
                                                                    onclick="deletePatient(<?php echo $pat['pat_id']; ?>, '<?php echo htmlspecialchars($pat['pat_first_name'] . ' ' . $pat['pat_last_name']); ?>')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-user-injured fa-2x mb-2 d-block"></i>
                                                        <?php echo $patients['status'] === 'error' ? 'Error: ' . htmlspecialchars($patients['message']) : 'No patients found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination for Patients -->
                                <?php if ($patients['status'] === 'success' && !empty($patients['data']) && count($patients['data']) > 10): ?>
                                <nav aria-label="Patients pagination">
                                    <ul class="pagination justify-content-center mt-3" id="patients-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Staff Tab -->
                        <div class="tab-pane fade" id="staff" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search staff..." 
                                                   id="search-staff">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddStaffModal()">
                                            <i class="fas fa-plus"></i> Add Staff
                                        </button>
                                    </div>
                                </div>

                                <!-- Staff Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover" id="staff-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th>Staff ID</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Email</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($staff_members['status'] === 'success' && !empty($staff_members['data'])): ?>
                                                <?php foreach ($staff_members['data'] as $staff_member): ?>
                                                <tr>
                                                    <td><?php echo $staff_member['staff_id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($staff_member['staff_first_name'] . ' ' . $staff_member['staff_last_name']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($staff_member['staff_contact_num'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($staff_member['staff_email'] ?? 'N/A'); ?></td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-warning btn-action" 
                                                                    onclick="openEditStaffModal(<?php echo $staff_member['staff_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-action"
                                                                    onclick="deleteStaff(<?php echo $staff_member['staff_id']; ?>, '<?php echo htmlspecialchars($staff_member['staff_first_name'] . ' ' . $staff_member['staff_last_name']); ?>')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="fas fa-user-tie fa-2x mb-2 d-block"></i>
                                                        <?php echo $staff_members['status'] === 'error' ? 'Error: ' . htmlspecialchars($staff_members['message']) : 'No staff members found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination for Staff -->
                                <?php if ($staff_members['status'] === 'success' && !empty($staff_members['data']) && count($staff_members['data']) > 10): ?>
                                <nav aria-label="Staff pagination">
                                    <ul class="pagination justify-content-center mt-3" id="staff-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Users Tab -->
                        <div class="tab-pane fade" id="users" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search user accounts..." 
                                                   id="search-users">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <div class="btn-group" role="group" aria-label="User type filter">
                                        <input type="radio" class="btn-check" name="userTypeFilter" id="filter-all" autocomplete="off" checked>
                                        <label class="btn btn-outline-teal" for="filter-all">
                                            <i class="fas fa-users me-1"></i>All Users
                                        </label>

                                        <input type="radio" class="btn-check" name="userTypeFilter" id="filter-doctors" autocomplete="off">
                                        <label class="btn btn-outline-teal" for="filter-doctors">
                                            <i class="fas fa-user-md me-1"></i>Doctors
                                        </label>

                                        <input type="radio" class="btn-check" name="userTypeFilter" id="filter-patients" autocomplete="off">
                                        <label class="btn btn-outline-teal" for="filter-patients">
                                            <i class="fas fa-user-injured me-1"></i>Patients
                                        </label>

                                        <input type="radio" class="btn-check" name="userTypeFilter" id="filter-staff" autocomplete="off">
                                        <label class="btn btn-outline-teal" for="filter-staff">
                                            <i class="fas fa-user-tie me-1"></i>Staff
                                        </label>
                                    
                                    </div>
                                         <!--
                                        <button class="btn btn-teal" onclick="openAddUserModal()">
                                            <i class="fas fa-plus"></i> Create User Account
                                        </button>
                                        -->
                                    </div>
                                    
                                </div>

                                <!-- Users Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th>User ID</th>
                                                <th>Username</th>
                                                <th>User Type</th>
                                                <th>Last Login</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($users['status'] === 'success' && !empty($users['data'])): ?>
                                                <?php foreach ($users['data'] as $usr): ?>
                                                <tr data-user-type="<?php echo htmlspecialchars($usr['user_type']); ?>">
                                                    <td><?php echo $usr['user_id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($usr['user_name']); ?></strong>
                                                        <?php if ($usr['user_is_superadmin']): ?>
                                                            <span class="badge bg-danger ms-1">Super Admin</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $type_class = 'badge';
                                                        $type_text = ucfirst($usr['user_type']);
                                                        
                                                        // Simple conditions - no complex logic
                                                        if ($usr['user_type'] === 'doctor') {
                                                            $type_class = 'pastel-blue';
                                                        } elseif ($usr['user_type'] === 'patient') {
                                                            $type_class = 'pastel-orange';
                                                        } elseif ($usr['user_type'] === 'staff') {
                                                            $type_class = 'pastel-green';
                                                        } elseif ($usr['user_type'] === 'superadmin') {
                                                            $type_class = 'pastel-pink';
                                                        } elseif ($usr['user_type'] === 'inactive') {
                                                            $type_class = 'pastel-gray';
                                                            $type_text = 'Inactive/Nullified';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $type_class; ?>">
                                                            <?php echo $type_text; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($usr['user_last_login']): ?>
                                                            <?php echo date('M j, Y g:i A', strtotime($usr['user_last_login'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Never</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('M j, Y', strtotime($usr['user_created_at'])); ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-user-circle fa-2x mb-2 d-block"></i>
                                                        <?php echo $users['status'] === 'error' ? 'Error: ' . htmlspecialchars($users['message']) : 'No user accounts found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination for Users -->
                                <?php if ($users['status'] === 'success' && !empty($users['data']) && count($users['data']) > 10): ?>
                                <nav aria-label="Users pagination">
                                    <ul class="pagination justify-content-center mt-3" id="users-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include '../components/user_modals.php'; ?>

    <!-- SweetAlert + JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/management.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality for each tab
    initializeSearch('search-doctors', 'doctors-table');
    initializeSearch('search-patients', 'patients-table');
    initializeSearch('search-staff', 'staff-table');
    initializeSearch('search-users', 'users-table');

    // Initialize user filters
    initializeUserFilters();
    
    // Initialize pagination
    initializePagination('doctors-table', 'doctors-pagination', 10);
    initializePagination('patients-table', 'patients-pagination', 10);
    initializePagination('staff-table', 'staff-pagination', 10);
    initializePagination('users-table', 'users-pagination', 10);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

    // Doctor Actions
    function openAddDoctorModal() {
        const modal = new bootstrap.Modal(document.getElementById('addDoctorModal'));
        fetch(`../handlers/users/user_handler.php?type=doctor_form`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('addDoctorModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load doctor form', 'error');
            });
    }

    function openEditDoctorModal(doctorId) {
        const modal = new bootstrap.Modal(document.getElementById('editDoctorModal'));
        fetch(`../handlers/users/user_handler.php?type=doctor_form&id=${doctorId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editDoctorModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load doctor form', 'error');
            });
    }

    function deleteDoctor(doctorId, doctorName) {
    confirmDelete(doctorId, doctorName, 'doc', '../pages/user_management.php');
    }

    // Patient Actions
    function openAddPatientModal() {
        const modal = new bootstrap.Modal(document.getElementById('addPatientModal'));
        fetch(`../handlers/users/user_handler.php?type=patient_form`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('addPatientModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load patient form', 'error');
            });
    }

    function openEditPatientModal(patientId) {
        const modal = new bootstrap.Modal(document.getElementById('editPatientModal'));
        fetch(`../handlers/users/user_handler.php?type=patient_form&id=${patientId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editPatientModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load patient form', 'error');
            });
    }

    function deletePatient(patientId, patientName) {
    confirmDelete(patientId, patientName, 'pat', '../pages/user_management.php');
    }

    // Staff Actions 
    function openAddStaffModal() {
        const modal = new bootstrap.Modal(document.getElementById('addStaffModal'));
        fetch(`../handlers/users/user_handler.php?type=staff_form`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('addStaffModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load staff form', 'error');
            });
    }

    function openEditStaffModal(staffId) {
        const modal = new bootstrap.Modal(document.getElementById('editStaffModal'));
        fetch(`../handlers/users/user_handler.php?type=staff_form&id=${staffId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editStaffModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load staff form', 'error');
            });
    }

    function deleteStaff(staffId, staffName) {
        confirmDelete(staffId, staffName, 'staff', '../pages/user_management.php');
    }

    // User Account Actions
function openAddUserModal(role = '', id = '') {
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));

    fetch(`../handlers/users/user_handler.php?type=user_account_form`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('addUserModalBody').innerHTML = html;
            modal.show();

            // 🧠 Automatically select the linked profile (doctor/patient/staff)
            if (role && id) {
                setTimeout(() => {
                    if (role === 'doctor') {
                        document.querySelector('select[name="doc_id"]').value = id;
                        document.querySelector('select[name="pat_id"]').disabled = true;
                        document.querySelector('select[name="staff_id"]').disabled = true;
                    } 
                    else if (role === 'patient') {
                        document.querySelector('select[name="pat_id"]').value = id;
                        document.querySelector('select[name="doc_id"]').disabled = true;
                        document.querySelector('select[name="staff_id"]').disabled = true;
                    } 
                    else if (role === 'staff') {
                        document.querySelector('select[name="staff_id"]').value = id;
                        document.querySelector('select[name="doc_id"]').disabled = true;
                        document.querySelector('select[name="pat_id"]').disabled = true;
                    }
                }, 300); // wait a bit for modal content to load
            }
        })
        .catch(error => {
            console.error('Error loading form:', error);
            Swal.fire('Error!', 'Failed to load user account form', 'error');
        });
}


    document.addEventListener('DOMContentLoaded', function() {
        // 1. Activate tab based on URL hash on page load
        const hash = window.location.hash;
        if (hash) {
            const tabButton = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
        
        // 2. UPDATE URL WHEN TABS ARE CLICKED MANUALLY (THIS IS WHAT'S MISSING)
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                // Update URL hash without page reload
                if (history.pushState) {
                    history.pushState(null, null, target);
                } else {
                    window.location.hash = target;
                }
            });
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>