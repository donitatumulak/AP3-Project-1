<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header("Location: ../handlers/auth/login.php");
    exit();
}

// Include database and classes
require_once '../config/Database.php';
require_once '../classes/users/Patient.php';
require_once '../classes/users/User.php';

// Get database connection
$database = new Database();
$db = $database->connect();

// Get patient data
$patient = new Patient($db);
$user = new User($db);

// Get patient's profile information
$patient_id = $_SESSION['profile_id'];
$user_info = $user->getUserWithProfile($_SESSION['user_id']);
$patient_data = $patient->getPatientByID($patient_id);

// Get all dashboard data through Patient class methods
$appointment_counts = $patient->getAppointmentCounts($patient_id);
$upcoming_appointments = $patient->getUpcomingAppointments($patient_id, 10);
$history_appointments = $patient->getAppointmentHistory($patient_id, 10);

// Extract counts with default values
$upcoming_count = $appointment_counts['upcoming_count'] ?? 0;
$completed_count = $appointment_counts['completed_count'] ?? 0;
$cancelled_count = $appointment_counts['cancelled_count'] ?? 0;
$doctors_count = $appointment_counts['doctors_count'] ?? 0;

$page = 'dashboards/patient_dashboard'; 
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
                            <h1>Welcome back, <?php echo htmlspecialchars($patient_data['data']['pat_first_name'] . ' ' . $patient_data['data']['pat_last_name']); ?>!</h1>
                            <p class="date-display" id="currentDate"></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-user-injured fa-3x opacity-75"></i>
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
                                        <div class="card-title">UPCOMING APPOINTMENTS</div>
                                        <div class="card-value"><?php echo $upcoming_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-calendar-check"></i>
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
                        <div class="card summary-card card-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">CANCELLED</div>
                                        <div class="card-value"><?php echo $cancelled_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-times-circle"></i>
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
                                        <div class="card-title">DOCTORS VISITED</div>
                                        <div class="card-value"><?php echo $doctors_count; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access Buttons -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-3"><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="col-6 col-sm-6 mb-3">
                        <a href="../pages/my_appointments.php" class="quick-access-btn text-decoration-none">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Book Appointment</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-6 mb-3">
                        <a href="../pages/my_medical_record.php" class="quick-access-btn text-decoration-none">
                            <i class="fas fa-file-medical"></i>
                            <span>View Medical Records</span>
                        </a>
                    </div>
                </div>
                
                <!-- Appointments Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2><i class="fa-solid fa-calendar-alt"></i> My Appointments</h2>
                                <div class="appointment-tabs">
                                    <button class="btn btn-tab active dash-btn" data-tab="upcoming">Upcoming (<?php echo $upcoming_count; ?>)</button>
                                    <button class="btn btn-tab dash-btn" data-tab="history">History (<?php echo $completed_count + $cancelled_count; ?>)</button>
                                </div>
                            </div>
                            
                            <!-- Upcoming Appointments Table -->
                            <div class="table-responsive tab-content active" id="upcoming-tab">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Doctor</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($upcoming_appointments)): ?>
                                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                                <tr>
                                                    <td><?php echo date('M j, Y', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo date('g:i A', strtotime($appointment['appt_time'])); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['serv_name']); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $appointment['status_name'])); ?>">
                                                            <?php echo htmlspecialchars($appointment['status_name']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                         <div class="btn-group btn-group-sm">
                                                            <?php if ($appointment['stat_id'] === '1' || $appointment['status_name'] == 'Scheduled'): ?>
                                                                <!-- Cancel Appointment -->
                                                                <button class="btn btn-outline-danger btn-action me-1" 
                                                                        title="Cancel Appointment" 
                                                                        onclick="cancelAppointment(<?php echo $appointment['appt_id']; ?>)">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                                
                                                                <!-- Reschedule Appointment -->
                                                                <button class="btn btn-outline-primary btn-action me-1" 
                                                                        title="Reschedule" 
                                                                        onclick="rescheduleAppointment(<?php echo $appointment['appt_id']; ?>)">
                                                                    <i class="fas fa-calendar-alt"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <!-- View Details -->
                                                            <button class="btn btn-outline-info btn-action me-1" 
                                                                    title="View Details" 
                                                                    onclick="viewAppointmentDetails(<?php echo $appointment['appt_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No upcoming appointments found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Appointment History Table -->
                            <div class="table-responsive tab-content" id="history-tab">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Doctor</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($history_appointments)): ?>
                                            <?php foreach ($history_appointments as $appointment): ?>
                                                <tr>
                                                    <td><?php echo date('M j, Y', strtotime($appointment['appt_date'])); ?></td>
                                                    <td><?php echo date('g:i A', strtotime($appointment['appt_time'])); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['serv_name']); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $appointment['status_name'])); ?>">
                                                            <?php echo htmlspecialchars($appointment['status_name']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                        <!-- View Details -->
                                                        <button class="btn btn-outline-info btn-action" 
                                                                title="View Details" 
                                                                onclick="viewAppointmentDetails(<?php echo $appointment['appt_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No appointment history found.</td>
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
// JavaScript remains the same...
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.btn-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            const targetContent = document.getElementById(targetTab + '-tab');
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});

// Cancel Appointment Function
// Cancel Appointment Function - IMPROVED ERROR HANDLING
function cancelAppointment(apptId) {
    Swal.fire({
        title: 'Cancel Appointment?',
        text: "Are you sure you want to cancel this appointment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'Keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Cancelling...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // AJAX call to cancel appointment
            fetch('../handlers/cancel_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ appt_id: apptId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Cancel response:", data); // Debug log
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
                        text: data.message,
                        confirmButtonColor: '#20c997'
                    }).then(() => {
                        location.reload(); // Refresh to show updated status
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to cancel appointment', 'error');
                }
            })
            .catch(error => {
                console.error('Cancel error:', error);
                Swal.fire('Error!', 'Network error: ' + error.message, 'error');
            });
        }
    });
}

// Reschedule Appointment Function
function rescheduleAppointment(apptId) {
    // Get tomorrow's date for min attribute
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    // Get date 3 months from now for max attribute
    const maxDate = new Date();
    maxDate.setMonth(maxDate.getMonth() + 3);
    const maxDateStr = maxDate.toISOString().split('T')[0];

    Swal.fire({
        title: 'Reschedule Appointment',
        html: `
            <div class="mb-3">
                <label for="newDate" class="form-label">New Date</label>
                <input type="date" id="newDate" class="form-control" 
                       min="${minDate}" max="${maxDateStr}" required>
            </div>
            <div class="mb-3">
                <label for="newTime" class="form-label">New Time</label>
                <select id="newTime" class="form-select" required>
                    <option value="">Select time</option>
                    <option value="08:00:00">08:00 AM</option>
                    <option value="09:00:00">09:00 AM</option>
                    <option value="10:00:00">10:00 AM</option>
                    <option value="11:00:00">11:00 AM</option>
                    <option value="13:00:00">01:00 PM</option>
                    <option value="14:00:00">02:00 PM</option>
                    <option value="15:00:00">03:00 PM</option>
                    <option value="16:00:00">04:00 PM</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Reschedule',
        confirmButtonColor: '#20c997',
        preConfirm: () => {
            const date = document.getElementById('newDate').value;
            const time = document.getElementById('newTime').value;
            
            if (!date) {
                Swal.showValidationMessage('Please select a date');
                return false;
            }
            if (!time) {
                Swal.showValidationMessage('Please select a time');
                return false;
            }
            
            return { date, time };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Rescheduling...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // AJAX call to reschedule
            fetch('../handlers/reschedule_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appt_id: apptId,
                    new_date: result.value.date,
                    new_time: result.value.time
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rescheduled!',
                        text: data.message,
                        confirmButtonColor: '#20c997'
                    }).then(() => {
                        location.reload(); // Refresh to show updated appointment
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}

// View Appointment Details Function
// View Appointment Details Function - USE YOUR ACTUAL FILE NAME
function viewAppointmentDetails(apptId) {
    console.log("Viewing appointment details for ID:", apptId);
    
    // Show loading
    Swal.fire({
        title: 'Loading...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // USE YOUR ACTUAL FILE NAME: get_appointment.php
    fetch('../handlers/get_appointment.php?appt_id=' + apptId)
        .then(response => {
            console.log("Response status:", response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Received data:", data);
            Swal.close();
            
            if (data.status === 'success' && data.data) {
                const appt = data.data;
                console.log("Appointment details:", appt);
                
                Swal.fire({
                    title: 'Appointment Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Appointment ID:</strong> ${appt.formatted_id || appt.appt_id}</p>
                            <p><strong>Date:</strong> ${new Date(appt.appt_date).toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            })}</p>
                            <p><strong>Time:</strong> ${appt.appt_time ? new Date('1970-01-01T' + appt.appt_time).toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            }) : 'N/A'}</p>
                            <p><strong>Doctor:</strong> ${appt.doctor_name || 'N/A'}</p>
                            <p><strong>Service:</strong> ${appt.service_name || 'N/A'}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeColor(appt.status_name)}">${appt.status_name || 'N/A'}</span></p>
                            ${appt.patient_name ? `<p><strong>Patient:</strong> ${appt.patient_name}</p>` : ''}
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonColor: '#20c997',
                    width: '600px'
                });
            } else {
                console.error("API returned error:", data.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Could not load appointment details',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Network Error!',
                text: 'Failed to load appointment details: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
        });
}

// Helper function for status badge colors
function getStatusBadgeColor(status) {
    const colors = {
        'Scheduled': 'primary',
        'Completed': 'success',
        'Cancelled': 'danger',
        'Confirmed': 'info',
        'Pending': 'warning'
    };
    return colors[status] || 'secondary';
}

</script>

<?php include '../includes/footer.php'; ?>