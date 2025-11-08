<?php
$page = 'pages/my_account';
session_start();
require_once '../config/Database.php';
require_once '../classes/users/User.php';
require_once '../classes/users/Doctor.php';
require_once '../classes/users/Staff.php';
require_once '../classes/users/Patient.php';

require_once '../includes/header.php';
require_once '../includes/sidebar_user.php';

$database = new Database();
$db = $database->connect();

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_type'];

$userObj = new User($db);
$userRes = $userObj->getUserById($user_id);

if ($userRes['status'] !== 'success' || empty($userRes['data'])) {
    echo "<div class='alert alert-danger text-center m-4'>Unable to retrieve user data.</div>";
    require_once '../includes/footer.php';
    exit();
}

$userData = $userRes['data'];
$profileData = [];

switch ($user_role) {
    case 'doctor':
        $doctorObj = new Doctor($db);
        $doctorRes = $doctorObj->getDoctorById($userData['doc_id']);
        $profileData = $doctorRes['data'] ?? [];
        break;

    case 'staff':
        $staffObj = new Staff($db);
        $staffRes = $staffObj->getStaffById($userData['staff_id']);
        $profileData = $staffRes['data'] ?? [];
        break;

    case 'patient':
        $patientObj = new Patient($db);
        $patientRes = $patientObj->getPatientById($userData['pat_id']);
        $profileData = $patientRes['data'] ?? [];
        break;

    default:
        echo "<div class='alert alert-danger text-center m-4'>Invalid role detected.</div>";
        require_once '../includes/footer.php';
        exit();
}

$mergedData = array_merge($userData, $profileData);
?>

<body class="account-page">
<div class="main-content p-4">
    <!-- SUCCESS/ERROR MESSAGE SECTION -->
    <?php
    // Display success/error messages
    if (isset($_SESSION['success'])) {
        echo '<div class="container mb-4">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>' . $_SESSION['success'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              </div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo '<div class="container mb-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>' . $_SESSION['error'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              </div>';
        unset($_SESSION['error']);
    }
    ?>

    <div class="container">
        <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 750px;">
            <div class="card-body p-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-teal m-0 mb-2 mb-md-0">My Account</h3>
                
                <div class="d-flex flex-wrap gap-2">
                    <a href="update_account.php" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil-square me-1"></i> Update Details
                    </a>
                    <a href="change_password.php" class="btn btn-outline-secondary">
                    <i class="fas fa-key me-1"></i> Change Password
                    </a>
                </div>
            </div>

                <!-- ACCOUNT SECTION -->
                <h5 class="text-muted mb-3 border-bottom pb-2">Account Information</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Username:</strong> <?= htmlspecialchars($mergedData['user_name'] ?? '') ?></p>
                        <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user_role)) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created On:</strong> <?= htmlspecialchars($mergedData['user_created_at'] ?? '') ?></p>
                        <p><strong>Last Login:</strong> <?= htmlspecialchars($mergedData['user_last_login'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <!-- PROFILE SECTION -->
                <h5 class="text-muted mb-3 border-bottom pb-2">Personal Information</h5>

                <?php if ($user_role === 'patient'): ?>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($mergedData['pat_first_name'] . ' ' . $mergedData['pat_last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($mergedData['pat_email'] ?? '') ?></p>
                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($mergedData['pat_contact_num'] ?? '') ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($mergedData['pat_gender'] ?? '') ?></p>
                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($mergedData['pat_dob'] ?? '') ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($mergedData['pat_address'] ?? '') ?></p>

                <?php elseif ($user_role === 'doctor'): ?>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($mergedData['doc_first_name'] . ' ' . $mergedData['doc_last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($mergedData['doc_email'] ?? '') ?></p>
                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($mergedData['doc_contact_num'] ?? '') ?></p>
                    <p><strong>Specialization:</strong> <?= htmlspecialchars($mergedData['spec_name'] ?? 'N/A') ?></p>

                <?php elseif ($user_role === 'staff'): ?>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($mergedData['staff_first_name'] . ' ' . $mergedData['staff_last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($mergedData['staff_email'] ?? '') ?></p>
                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($mergedData['staff_contact_num'] ?? '') ?></p>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="update_account.php" class="btn btn-teal px-4">
                        <i class="bi bi-pencil me-1"></i> Edit My Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>