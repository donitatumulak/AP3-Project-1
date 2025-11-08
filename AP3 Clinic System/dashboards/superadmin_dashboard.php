<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header("Location: ../handlers/auth/login.php");
    exit();
}

// Include database and classes using autoloader
require_once '../config/Database.php'; 
require_once '../classes/users/User.php'; 
require_once '../classes/appointments/Appointment.php';

// Get database connection
$database = new Database();
$db = $database->connect();

// Initialize classes
$user = new User($db);
$appointment = new Appointment($db);

// Get user counts
$doctorUsers = $user->getDoctorUsers();
$patientUsers = $user->getPatientUsers();
$staffUsers = $user->getStaffUsers();

// Count totals
$totalDoctors = $doctorUsers['status'] === 'success' ? count($doctorUsers['data']) : 0;
$totalPatients = $patientUsers['status'] === 'success' ? count($patientUsers['data']) : 0;
$totalStaff = $staffUsers['status'] === 'success' ? count($staffUsers['data']) : 0;

// Get appointment data using Appointment class methods
$appointmentsTotal = $appointment->getTotalAppointments();
$totalAppointments = $appointmentsTotal['total'] ?? 0;

// Get recent appointments with status names
$recentAppointments = $appointment->getRecentAppointments(10);
$page = 'dashboards/superadmin_dashboard'; 
include '../includes/header.php'; 
?>

<body class="dashboard-page">
  <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar_user.php'; ?>
            <!-- Main Content -->
            <div class="col-lg-10 main-content">
                <!-- Welcome Header -->
                <div class="welcome-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1>Welcome back, <?php echo $_SESSION['full_name']; ?>!</h1>
                            <p class="date-display" id="currentDate"></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fa-solid fa-user-tie fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-1">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL DOCTORS</div>
                                        <div class="card-value"><?php echo $totalDoctors; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL STAFF</div>
                                        <div class="card-value"><?php echo $totalStaff; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-nurse"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL PATIENTS</div>
                                        <div class="card-value"><?php echo $totalPatients; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-injured"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL APPOINTMENTS</div>
                                        <div class="card-value"><?php echo $totalAppointments; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access Buttons -->
                <div class="row mb-4"  id="dashboard-content">
                    <div class="col-12">
                        <h2 class="mb-3"><i class="fa-solid fa-bolt"></i> Quick Access</h2>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                        <a class="quick-access-btn text-decoration-none" href = "../pages/user_management.php#doctors-tab">
                            <i class="fas fa-user-md"></i>
                            <span>Add Doctor</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                        <a class="quick-access-btn text-decoration-none" href = "../pages/user_management.php#patients-tab">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Patient</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                        <a class="quick-access-btn text-decoration-none" href = "../pages/user_management.php#staff-tab">
                            <i class="fas fa-user-nurse"></i>
                            <span>Add Staff</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                        <a class="quick-access-btn text-decoration-none" href = "../pages/user_management.php#users-tab">
                            <i class="fas fa-users-cog"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                        <a class="quick-access-btn text-decoration-none" href = "../pages/appointment_management.php#doctors-tab">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Manage Appointments</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                        <a class="quick-access-btn text-decoration-none" href = "../pages/payment_management.php">
                            <i class="fa-solid fa-money-bill"></i>
                            <span>Manage Payments</span>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Appointments Table - WITH STATUS NAMES -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2><i class="fa-solid fa-calendar-day"></i> Recent Appointments</h2>
                                <a href = "../pages/appointment_management.php#appointments-table" class="btn btn-outline dash-btn">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentAppointments)): ?>
                                            <?php foreach ($recentAppointments as $appointment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($appointment['pat_first_name'] . ' ' . $appointment['pat_last_name']); ?></td>
                                                    <td>Dr. <?php echo htmlspecialchars($appointment['doc_first_name'] . ' ' . $appointment['doc_last_name']); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo date('g:i A', strtotime($appointment['appt_time'])); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower($appointment['stat_name']); ?>">
                                                            <?php echo htmlspecialchars($appointment['stat_name']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No recent appointments found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include '../includes/footer.php'; ?>