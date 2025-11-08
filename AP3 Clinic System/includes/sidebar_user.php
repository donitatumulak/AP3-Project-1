<?php
// Get user information from session
$user_type = $_SESSION['user_type'] ?? '';
$user_name = $_SESSION['user_name'] ?? '';
$full_name = $_SESSION['full_name'] ?? $user_name;

// Profile picture and role title
switch ($user_type) {
    case 'superadmin':
        $profile_pic = '../assets/images/admin_cat.jpg';
        $role_title = 'Super Admin';
        break;
    case 'doctor':
        $profile_pic = '../assets/images/doctor1_cat.jpg';
        $role_title = 'Medical Doctor';
        break;
    case 'staff':
        $profile_pic = '../assets/images/staff_cat.jpg';
        $role_title = 'Clinic Staff';
        break;
    case 'patient':
        $profile_pic = '../assets/images/patient_cat.jpg';
        $role_title = 'Patient';
        break;
    default:
        $profile_pic = '../assets/images/user_photo.jpg';
        $role_title = 'User';
}
?>

<!-- Sidebar -->
<body>
<div class="col-lg-2 px-0 sidebar">
    <!-- Profile header -->
    <div class="admin-profile text-center py-3">
        <img src="<?= $profile_pic; ?>" alt="Profile Photo" class="admin-photo rounded-circle">
        <h5 class="mt-2 mb-0"><?= htmlspecialchars($full_name); ?></h5>
        <p class="text-muted small mb-0"><?= $role_title; ?></p>
    </div>

    <nav class="nav flex-column p-3">

        <!-- DASHBOARD -->
        <a class="nav-link-side <?= $page === 'dashboards/dashboard' ? 'active' : ''; ?>" 
           href="../dashboards/dashboard.php">
           <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>

        <!-- USER MANAGEMENT (superadmin only) -->
        <?php if ($user_type === 'superadmin'): ?>
        <a class="nav-link-side <?= $page === 'pages/user_management' ? 'active' : ''; ?>" 
           href="../pages/user_management.php">
           <i class="fas fa-users-cog me-2"></i> User Management
        </a>
        <?php endif; ?>

        <!-- APPOINTMENT MANAGEMENT (superadmin, staff) -->
        <?php if (in_array($user_type, ['superadmin', 'staff'])): ?>
            <a class="nav-link-side <?= $page === 'pages/appointment_management' ? 'active' : ''; ?>" 
            href="../pages/appointment_management.php">
            <i class="fas fa-calendar-check me-2"></i> 
            <?= $user_type === 'staff' ? 'Status' : 'Appointments'; ?>
            </a>
        <?php endif; ?>

        <!-- MEDICAL RECORDS MANAGEMENT (superadmin, staff) -->
        <?php if (in_array($user_type, ['superadmin', 'staff'])): ?>
        <a class="nav-link-side <?= $page === 'pages/medical_records_management' ? 'active' : ''; ?>" 
           href="../pages/medical_records_management.php">
           <i class="fas fa-notes-medical me-2"></i> Medical Records
        </a>
        <?php endif; ?>

        <!-- PAYMENTS (superadmin, staff) -->
        <?php if (in_array($user_type, ['superadmin', 'staff'])): ?>
        <a class="nav-link-side <?= $page === 'pages/payment_management' ? 'active' : ''; ?>" 
           href="../pages/payment_management.php">
           <i class="fas fa-credit-card me-2"></i> Payments
        </a>
        <?php endif; ?>

        <!-- SERVICES (superadmin, staff) -->
        <?php if (in_array($user_type, ['superadmin', 'staff'])): ?>
        <a class="nav-link-side <?= $page === 'pages/services_management' ? 'active' : ''; ?>" 
           href="../pages/services_management.php">
           <i class="fas fa-stethoscope me-2"></i> Services
        </a>
        <?php endif; ?>

        <!-- DOCTOR PAGES -->
        <?php if ($user_type === 'doctor'): ?>
            <hr>
            <a class="nav-link-side <?= $page === 'pages/my_doc_appointments' ? 'active' : ''; ?>" 
               href="../pages/my_doc_appointments.php">
               <i class="fas fa-calendar-alt me-2"></i> My Appointments
            </a>

            <a class="nav-link-side <?= $page === 'pages/my_doc_medical_records' ? 'active' : ''; ?>" 
               href="../pages/my_doc_medical_records.php">
               <i class="fas fa-file-medical me-2"></i> My Medical Records
            </a>

            <a class="nav-link-side <?= $page === 'pages/my_schedule' ? 'active' : ''; ?>" 
               href="../pages/my_schedule.php">
               <i class="fas fa-calendar-day me-2"></i> My Schedule
            </a>
        <?php endif; ?>

        <!-- PATIENT PAGES -->
        <?php if ($user_type === 'patient'): ?>
            <hr>
            <a class="nav-link-side <?= $page === 'pages/my_appointments' ? 'active' : ''; ?>" 
               href="../pages/my_appointments.php">
               <i class="fas fa-calendar-alt me-2"></i> My Appointments
            </a>

            <a class="nav-link-side <?= $page === 'pages/my_medical_record' ? 'active' : ''; ?>" 
               href="../pages/my_medical_record.php">
               <i class="fas fa-file-medical me-2"></i> My Medical Records
            </a>
        <?php endif; ?>

        <!-- ACCOUNT (staff, doctor, patient) -->
        <?php if (in_array($user_type, ['staff', 'doctor', 'patient'])): ?>
        <hr>
        <a class="nav-link-side <?= $page === 'pages/my_account' ? 'active' : ''; ?>" 
           href="../pages/my_account.php">
           <i class="fas fa-user-circle me-2"></i> My Account
        </a>
        <?php endif; ?>

        <!-- LOGOUT -->
        <a class="nav-link-side logout-link mt-auto" href="../handlers/auth/logout.php">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </nav>
</div>

<button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
  <i class="fas fa-bars"></i>
</button>
</body>

<style>
.sidebar {
    background-color: #fff;
    border-right: 1px solid #e0e0e0;
    height: 100vh;
    position: fixed;
}

.admin-photo {
    width: 75px;
    height: 75px;
    object-fit: cover;
    border: 2px solid #009688;
}

.nav-link-side {
    color: #444;
    font-weight: 500;
    padding: 0.6rem 1rem;
    display: flex;
    align-items: center;
    transition: 0.3s;
    border-radius: 6px;
    text-decoration: none;
}

.nav-link-side:hover {
    background-color: #009688;
    color: white;
}

.nav-link-side.active {
    background-color: #009688;
    color: #fff;
}

.logout-link {
    color: #d9534f;
    font-weight: 600;
}

.sidebar-toggle {
    display: none;
    background: #009688;
    color: white;
    border: none;
    border-radius: 50%;
    padding: 8px 10px;
    position: fixed;
    bottom: 20px;
    left: 20px;
}

@media (max-width: 991px) {
    .sidebar {
        display: none;
        position: absolute;
        z-index: 1000;
        width: 250px;
    }
    .sidebar.show {
        display: block;
    }
    .sidebar-toggle {
        display: block;
    }
}
</style>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
}
</script>
