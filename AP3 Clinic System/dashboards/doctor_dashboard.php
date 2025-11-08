<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: ../handlers/auth/login.php");
    exit();
}

// Include database and classes
require_once '../config/Database.php';
require_once '../classes/users/Doctor.php';
require_once '../classes/users/User.php';

// Get database connection
$database = new Database();
$db = $database->connect();

// Get doctor and user data
$doctor = new Doctor($db);
$user = new User($db);

// Get doctor's profile information
$doctor_id = $_SESSION['profile_id'];
$user_info = $user->getUserWithProfile($_SESSION['user_id']);

// Get appointment counts and data
$todays_appointments = $doctor->getTodaysAppointments($doctor_id, 10);
$future_appointments = $doctor->getFutureAppointments($doctor_id, 10);
$previous_appointments = $doctor->getPreviousAppointments($doctor_id, 10);

// Calculate counts
$todays_count   = $todays_appointments['status'] === 'success' ? count($todays_appointments['data']) : 0;
$upcoming_count = $future_appointments['status'] === 'success' ? count($future_appointments['data']) : 0;
$completed_count = $previous_appointments['status'] === 'success' ? count($previous_appointments['data']) : 0;

// Pending count (optional: can be added to Doctor class later)
$pendingQuery = "
    SELECT COUNT(*) AS pending_count 
    FROM appointment a 
    INNER JOIN status st ON a.stat_id = st.stat_id 
    WHERE a.doc_id = :doc_id AND st.stat_name = 'Scheduled'
";
$pendingStmt = $db->prepare($pendingQuery);
$pendingStmt->bindParam(':doc_id', $doctor_id, PDO::PARAM_INT);
$pendingStmt->execute();
$pendingResult = $pendingStmt->fetch(PDO::FETCH_ASSOC);
$pending_count = $pendingResult['pending_count'] ?? 0;

$page = 'dashboards/doctor_dashboard';
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
                            <i class="fa-solid fa-user-doctor fa-3x opacity-75"></i>
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
                                        <div class="card-title">TODAY'S APPOINTMENTS</div>
                                        <div class="card-value"><?php echo $todays_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-calendar-day"></i>
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
                                        <div class="card-title">UPCOMING</div>
                                        <div class="card-value"><?php echo $upcoming_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-calendar-week"></i>
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
                                        <div class="card-title">COMPLETED</div>
                                        <div class="card-value"><?php echo $completed_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-check-circle"></i>
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
                                        <div class="card-title">PENDING</div>
                                        <div class="card-value"><?php echo $pending_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-3"><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
                    </div>

                    <div class="col-6 mb-3">
                        <a href="../pages/my_doc_medical_records.php" class="quick-access-btn text-decoration-none">
                            <i class="fas fa-file-medical"></i>
                            <span>View Medical Record</span>
                        </a>
                    </div>

                    <div class="col-6 mb-3">
                        <a href="../pages/my_schedule.php" class="quick-access-btn text-decoration-none">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span>Update Schedule</span>
                        </a>
                    </div>
                </div>

                <!-- Appointments Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2><i class="fa-solid fa-calendar-day"></i> My Appointments</h2>
                                <div class="appointment-tabs">
                                    <button class="btn btn-tab active dash-btn" data-tab="today">
                                        Today (<?php echo $todays_count; ?>)
                                    </button>
                                    <button class="btn btn-tab dash-btn" data-tab="upcoming">
                                        Upcoming (<?php echo $upcoming_count; ?>)
                                    </button>
                                </div>
                            </div>

                            <!-- Today's Appointments -->
                            <div class="table-responsive tab-content active" id="today-tab">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($todays_appointments['status'] === 'success' && !empty($todays_appointments['data'])): ?>
                                            <?php foreach ($todays_appointments['data'] as $appointment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($appointment['patient_full_name']); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo date('g:i A', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $appointment['status_name'])); ?>">
                                                            <?php echo htmlspecialchars($appointment['status_name']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <!-- Mark as Completed -->
                                                            <?php if ($appointment['status_name'] == 'Scheduled'): ?>
                                                            <button class="btn btn-outline-success btn-action me-1" 
                                                                    onclick="completeAppointment(<?php echo $appointment['appt_id']; ?>)" 
                                                                    title="Mark Completed">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Cancel Appointment -->
                                                            <?php if ($appointment['status_name'] == 'Scheduled'): ?>
                                                            <button class="btn btn-outline-danger btn-action me-1" 
                                                                    onclick="cancelAppointment(<?php echo $appointment['appt_id']; ?>)" 
                                                                    title="Cancel Appointment">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    No appointments scheduled for today.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Upcoming Appointments -->
                            <div class="table-responsive tab-content" id="upcoming-tab">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($future_appointments['status'] === 'success' && !empty($future_appointments['data'])): ?>
                                            <?php foreach ($future_appointments['data'] as $appointment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($appointment['patient_full_name']); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo date('g:i A', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $appointment['status_name'])); ?>">
                                                            <?php echo htmlspecialchars($appointment['status_name']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <!-- Cancel Appointment -->
                                                                <?php if ($appointment['status_name'] == 'Scheduled'): ?>
                                                                <button class="btn btn-outline-danger btn-action me-1"  
                                                                        onclick="cancelAppointment(<?php echo $appointment['appt_id']; ?>)" 
                                                                        title="Cancel Appointment">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    No upcoming appointments found.
                                                </td>
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

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.btn-tab');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.btn-tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab') + '-tab';
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });

    // Complete Appointment (Mark as Done)
    function completeAppointment(apptId) {
        Swal.fire({
            title: 'Complete Appointment?',
            text: "Mark this appointment as completed?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, complete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Completing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('../handlers/update_appointment_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ 
                        appt_id: apptId, 
                        stat_id: 2 // Assuming 2 = Completed
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Completed!',
                            text: data.message,
                            confirmButtonColor: '#28a745'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Network error: ' + error.message, 'error');
                });
            }
        });
    }

    // Cancel Appointment (Doctor side)
    function cancelAppointment(apptId) {
        Swal.fire({
            title: 'Cancel Appointment?',
            text: "Are you sure you want to cancel this appointment?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'Keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cancelling...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('../handlers/update_appointment_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ 
                        appt_id: apptId, 
                        stat_id: 3 // Assuming 3 = Cancelled
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelled!',
                            text: data.message,
                            confirmButtonColor: '#dc3545'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Network error: ' + error.message, 'error');
                });
            }
        });
    }

    // View Appointment Details (Reuse from patient dashboard)
    function viewAppointmentDetails(apptId) {
        // Use the same function from patient dashboard
        fetch('../handlers/get_appointment.php?appt_id=' + apptId)
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.status === 'success' && data.data) {
                    const appt = data.data;
                    Swal.fire({
                        title: 'Appointment Details',
                        html: `
                            <div class="text-start">
                                <p><strong>Date:</strong> ${new Date(appt.appt_date).toLocaleDateString()}</p>
                                <p><strong>Time:</strong> ${appt.appt_time ? new Date('1970-01-01T' + appt.appt_time).toLocaleTimeString() : 'N/A'}</p>
                                <p><strong>Patient:</strong> ${appt.patient_name || 'N/A'}</p>
                                <p><strong>Service:</strong> ${appt.service_name || 'N/A'}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeColor(appt.status_name)}">${appt.status_name || 'N/A'}</span></p>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonColor: '#6c757d'
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Could not load details', 'error');
                }
            });
    }

    // Helper function for status colors
    function getStatusBadgeColor(status) {
        const colors = {
            'Scheduled': 'primary',
            'Completed': 'success', 
            'Cancelled': 'danger',
            'Confirmed': 'info'
        };
        return colors[status] || 'secondary';
    }
        </script>

<?php include '../includes/footer.php'; ?>
