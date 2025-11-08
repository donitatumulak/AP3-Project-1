<?php
session_start();
$page = 'pages/my_doc_appointments';
require_once '../config/Database.php';
require_once '../classes/appointments/Appointment.php';
require_once '../classes/users/Doctor.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_user.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. Doctors only.</div>";
    require_once '../includes/footer.php';
    exit();
}

$database = new Database();
$db = $database->connect();
$appointment = new Appointment($db);
$doctor = new Doctor($db);

$doc_id = $_SESSION['profile_id'] ?? null;
if (!$doc_id) {
    echo "<div class='alert alert-warning text-center m-4'>Doctor information missing in session.</div>";
    require_once '../includes/footer.php';
    exit();
}

// Fetch appointments
$todays_appointments   = $doctor->getTodaysAppointments($doc_id);
$future_appointments   = $doctor->getFutureAppointments($doc_id);
$previous_appointments = $doctor->getPreviousAppointments($doc_id);
?>

<body class="account-page">
<div class="main-content p-4">
    <div class="container">

        <!-- HEADER CARD -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 bg-teal text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">My Appointments</h3>
                    <p class="mb-0">View and manage your appointments easily</p>
                </div>
                <i class="fas fa-user-md fa-3x opacity-75"></i>
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">

                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs mb-4" id="appointmentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="today-tab" data-bs-toggle="tab" 
                                        data-bs-target="#today-content" type="button" role="tab">
                                    Today's Appointments
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" 
                                        data-bs-target="#upcoming-content" type="button" role="tab">
                                    Upcoming
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="past-tab" data-bs-toggle="tab" 
                                        data-bs-target="#past-content" type="button" role="tab">
                                    Past Appointments
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="appointmentTabContent">
                            <!-- TODAY'S APPOINTMENTS TAB -->
                            <div class="tab-pane fade show active" id="today-content" role="tabpanel">
                                <div class="card border-0 rounded-4 mb-3">
                                    <?php if ($todays_appointments['status'] === 'success' && !empty($todays_appointments['data'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" id="today-appointments-table">
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
                                                <?php foreach ($todays_appointments['data'] as $appt): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($appt['patient_full_name']); ?></strong></td>
                                                    <td><?= date('M j, Y', strtotime($appt['appt_date'])); ?></td>
                                                    <td><?= date('g:i A', strtotime($appt['appt_date'])); ?></td>
                                                    <td><?= htmlspecialchars($appt['service_name']); ?></td>
                                                    <td><span class="badge status-<?= strtolower($appt['status_name']); ?>"><?= htmlspecialchars($appt['status_name']); ?></span></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($appt['status_name'] == 'Scheduled'): ?>
                                                                <button class="btn btn-outline-success btn-action me-1" onclick="confirmComplete(<?= $appt['appt_id']; ?>)">
                                                                    <i class="fas fa-check-circle"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-action me-1" onclick="cancelAppointment(<?= $appt['appt_id']; ?>)">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-outline-info btn-action me-1" onclick="viewAppointmentDetails(<?= $appt['appt_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- JS Pagination container -->
                                    <?php if (count($todays_appointments['data']) > 10): ?>
                                    <nav aria-label="Today's appointments pagination">
                                        <ul class="pagination justify-content-center mt-3" id="today-pagination">
                                            <!-- Pagination generated by JS -->
                                        </ul>
                                    </nav>
                                    <?php endif; ?>

                                    <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-day fa-2x mb-2"></i>
                                        <p>No appointments scheduled for today.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- UPCOMING APPOINTMENTS TAB -->
                            <div class="tab-pane fade" id="upcoming-content" role="tabpanel">
                                <div class="card border-0 rounded-4 mb-3">
                                    <?php if ($future_appointments['status'] === 'success' && !empty($future_appointments['data'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" id="upcoming-appointments-table">
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
                                                <?php foreach ($future_appointments['data'] as $appt): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($appt['patient_full_name']); ?></strong></td>
                                                    <td><?= date('M j, Y', strtotime($appt['appt_date'])); ?></td>
                                                    <td><?= date('g:i A', strtotime($appt['appt_date'])); ?></td>
                                                    <td><?= htmlspecialchars($appt['service_name']); ?></td>
                                                    <td><span class="badge status-<?= strtolower($appt['status_name']); ?>"><?= htmlspecialchars($appt['status_name']); ?></span></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($appt['status_name'] == 'Scheduled'): ?>
                                                                <button class="btn btn-outline-danger btn-action me-1" onclick="cancelAppointment(<?= $appt['appt_id']; ?>)">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-outline-info btn-action me-1" onclick="viewAppointmentDetails(<?= $appt['appt_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- JS Pagination container -->
                                    <?php if (count($future_appointments['data']) > 10): ?>
                                    <nav aria-label="Upcoming appointments pagination">
                                        <ul class="pagination justify-content-center mt-3" id="upcoming-pagination">
                                            <!-- Pagination generated by JS -->
                                        </ul>
                                    </nav>
                                    <?php endif; ?>

                                    <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                        <p>No upcoming appointments found.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- PAST APPOINTMENTS TAB -->
                            <div class="tab-pane fade" id="past-content" role="tabpanel">
                                <div class="card border-0 rounded-4 mb-3">
                                    <?php if ($previous_appointments['status'] === 'success' && !empty($previous_appointments['data'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" id="past-appointments-table">
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
                                                <?php foreach ($previous_appointments['data'] as $appt): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($appt['patient_full_name']); ?></strong></td>
                                                    <td><?= date('M j, Y', strtotime($appt['appt_date'])); ?></td>
                                                    <td><?= date('g:i A', strtotime($appt['appt_date'])); ?></td>
                                                    <td><?= htmlspecialchars($appt['service_name']); ?></td>
                                                    <td><span class="badge status-<?= strtolower($appt['status_name']); ?>"><?= htmlspecialchars($appt['status_name']); ?></span></td>
                                                    <td>
                                                        <button class="btn btn-outline-info btn-sm" onclick="viewAppointmentDetails(<?= $appt['appt_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- JS Pagination container -->
                                    <?php if (count($previous_appointments['data']) > 10): ?>
                                    <nav aria-label="Past appointments pagination">
                                        <ul class="pagination justify-content-center mt-3" id="past-pagination">
                                            <!-- Pagination generated by JS -->
                                        </ul>
                                    </nav>
                                    <?php endif; ?>

                                    <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <p>No past appointments found.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="../public/js/management.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePagination('today-appointments-table', 'today-pagination', 10);
    initializePagination('upcoming-appointments-table', 'upcoming-pagination', 10);
    initializePagination('past-appointments-table', 'past-pagination', 10);

    // Reinitialize when switching tabs
    const tabButtons = document.querySelectorAll('#appointmentTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            const target = event.target.getAttribute('data-bs-target');
            setTimeout(() => {
                switch(target) {
                    case '#today-content':
                        initializePagination('today-appointments-table', 'today-pagination', 10);
                        break;
                    case '#upcoming-content':
                        initializePagination('upcoming-appointments-table', 'upcoming-pagination', 10);
                        break;
                    case '#past-content':
                        initializePagination('past-appointments-table', 'past-pagination', 10);
                        break;
                }
            }, 100);
        });
    });
});

// Appointment Actions
function cancelAppointment(apptId) {
    Swal.fire({
        title: 'Cancel Appointment?',
        text: 'Are you sure you want to cancel this appointment?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'Keep it'
    }).then(result => {
        if (result.isConfirmed) {
            updateAppointmentStatus(apptId, 'Cancelled');
        }
    });
}

function confirmComplete(apptId) {
    Swal.fire({
        title: 'Mark as Completed?',
        text: 'Are you sure you want to mark this appointment as completed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark it complete!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then(result => {
        if (result.isConfirmed) {
            updateAppointmentStatus(apptId, 'completed');
        }
    });
}


function updateAppointmentStatus(apptId, action) {
    const statusMap = {
        'Completed': 2,
        'Cancelled': 3
    };

    fetch('../handlers/update_appointment_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
            appt_id: apptId, 
            stat_id: statusMap[action] 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Success!', data.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error!', 'Network error occurred: ' + error.message, 'error');
    });
}

function viewAppointmentDetails(apptId) {
    fetch(`../handlers/get_appointment.php?appt_id=${apptId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                const appt = data.data;
                Swal.fire({
                    title: 'Appointment Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Patient:</strong> ${appt.patient_name || 'N/A'}</p>
                            <p><strong>Date:</strong> ${new Date(appt.appt_date).toLocaleDateString()}</p>
                            <p><strong>Time:</strong> ${new Date(appt.appt_date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                            <p><strong>Service:</strong> ${appt.service_name || 'N/A'}</p>
                            <p><strong>Status:</strong> ${appt.status_name || 'N/A'}</p>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Close'
                });
            } else {
                Swal.fire('Error', data.message || 'Could not load appointment details', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Failed to load appointment details', 'error');
        });
}
</script>

<style>
.status-scheduled { background-color: #fff3cd; color: #856404; }
.status-completed { background-color: #d1edff; color: #0c5460; }
.status-cancelled { background-color: #f8d7da; color: #721c24; }
.btn-action { padding: 0.25rem 0.5rem; }
</style>

<?php require_once '../includes/footer.php'; ?>
