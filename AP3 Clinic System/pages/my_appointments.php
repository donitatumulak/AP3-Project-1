<?php
$page = 'pages/my_appointments';
session_start();
require_once '../config/Database.php';
require_once '../classes/appointments/Appointment.php';
require_once '../classes/users/Patient.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_user.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'patient') {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. Patients only.</div>";
    require_once '../includes/footer.php';
    exit();
}

$database = new Database();
$db = $database->connect();
$appointment = new Appointment($db);
$patient = new Patient($db);

$pat_id = $_SESSION['pat_id'] ?? null;
if (!$pat_id) {
    echo "<div class='alert alert-warning text-center m-4'>No patient information found in session.</div>";
    require_once '../includes/footer.php';
    exit();
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'add_appointment':
            $result = $appointment->addAppointment(
                $_POST['appt_date'],
                $_POST['appt_time'],
                $pat_id,
                $_POST['doc_id'],
                $_POST['serv_id'],
                $_POST['stat_id'] ?? 1
            );
            $message = $result['message'];
            break;

        case 'update_appointment':
            $result = $appointment->updateAppointment(
                $_POST['appt_id'],
                $_POST['appt_time'],
                $_POST['appt_date'],
                $_POST['doc_id'],
                $_POST['serv_id'],
                $pat_id,
                $_POST['stat_id'] ?? 1
            );
            $message = $result['message'];
            break;

        case 'cancel_appointment':
            $result = $appointment->cancelAppointment($_POST['appt_id']);
            $message = $result['message'];
            break;
    }

    //echo "<div class='alert alert-info text-center'>$message</div>";
}

// Fetch services and appointments
$services = $appointment->getAllServices();
$doctors = $appointment->getAllDoctors();
$upcoming_appointments = $patient->getUpcomingAppointments($pat_id, null);
$history_appointments = $patient->getAppointmentHistory($pat_id, null);
?>

<body class="account-page">
<div class="main-content p-4">
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info alert-dismissible fade show text-center shadow-sm rounded-3 mx-auto" 
                role="alert" 
                style="max-width: 1000px;">
                <?= htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- HEADER CARD -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 bg-teal text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">My Appointments</h3>
                    <p class="mb-0">View, schedule, or manage your appointments easily</p>
                </div>
                <i class="fas fa-calendar-check fa-3x opacity-75"></i>
            </div>
        </div>

        <!-- BOOK NEW APPOINTMENT -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <h5 class="fw-bold text-teal mb-3"><i class="fas fa-plus-circle me-2"></i>Book New Appointment</h5>
                <form method="POST" autocomplete="off" class="row g-3">
                    <input type="hidden" name="action" value="add_appointment">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Select Service</label>
                        <select name="serv_id" class="form-select" required>
                            <option value="" disabled selected>Choose service</option>
                            <?php
                            if (!empty($services['data'])) {
                                foreach ($services['data'] as $serv) {
                                    echo "<option value='{$serv['serv_id']}'>{$serv['serv_name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Select Doctor</label>
                        <select name="doc_id" class="form-select" required>
                            <option value="" disabled selected>Choose doctor</option>
                            <?php
                            if (!empty($doctors['data'])) {
                                foreach ($doctors['data'] as $doc) {
                                    echo "<option value='{$doc['doc_id']}'>Dr. {$doc['doc_first_name']} {$doc['doc_last_name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="appt_date" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Time</label>
                        <input type="time" name="appt_time" class="form-control" required>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-teal px-4">
                            <i class="fas fa-calendar-plus me-1"></i>Book Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- APPOINTMENT TABS -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <h5 class="fw-bold text-teal mb-3"><i class="fas fa-list-alt me-2"></i>My Appointments</h5>

                <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab"
                                data-bs-target="#upcoming" type="button" role="tab">
                            Upcoming Appointments
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab"
                                data-bs-target="#history" type="button" role="tab">
                            Appointment History
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="appointmentTabsContent">
                    <!-- Upcoming Appointments Tab -->
                    <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                        <?php
                        // Limit and slicing removed; we'll handle pagination with JS
                        ?>
                        <?php if (!empty($upcoming_appointments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="upcoming-appointments-table">
                                <thead class="table-teal">
                                    <tr>
                                        <th>ID</th>
                                        <th>Doctor</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_appointments as $appt): ?>
                                    <tr>
                                        <td><span class="badge pastel-green"><?= $appointment->formatAppointmentId($appt['appt_id'], $appt['appt_date']); ?></span></td>
                                        <td><strong><?= htmlspecialchars($appt['doctor_name']); ?></strong></td>
                                        <td><?= htmlspecialchars($appt['serv_name']); ?></td>
                                        <td><?= date('M j, Y', strtotime($appt['appt_date'])); ?></td>
                                        <td><?= date('g:i A', strtotime($appt['appt_time'])); ?></td>
                                        <td><span class="badge pastel-orange"><?= htmlspecialchars($appt['status_name']); ?></span></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmCancel(<?= $appt['appt_id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                onclick="openUpdateModal(<?= $appt['appt_id']; ?>,'<?= $appt['serv_name']; ?>','<?= $appt['doctor_name']; ?>','<?= $appt['appt_date']; ?>','<?= $appt['appt_time']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick='viewAppointmentDetails(<?= json_encode($appt); ?>)'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- JS Pagination container -->
                        <?php if (count($upcoming_appointments) > 10): ?>
                        <nav aria-label="Upcoming appointments pagination">
                            <ul class="pagination justify-content-center mt-3" id="upcoming-pagination">
                                <!-- Pagination will be generated by JS -->
                            </ul>
                        </nav>
                        <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-info text-center shadow-sm rounded-3">
                                No upcoming appointments.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- History Appointments Tab -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <?php if (!empty($history_appointments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="history-appointments-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Doctor</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th class="text-center">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history_appointments as $appt): ?>
                                    <tr>
                                        <td><span class="badge pastel-green"><?= $appointment->formatAppointmentId($appt['appt_id'], $appt['appt_date']); ?></span></td>
                                        <td><strong><?= htmlspecialchars($appt['doctor_name']); ?></strong></td>
                                        <td><?= htmlspecialchars($appt['serv_name']); ?></td>
                                        <td><?= date('M j, Y', strtotime($appt['appt_date'])); ?></td>
                                        <td><?= date('g:i A', strtotime($appt['appt_time'])); ?></td>
                                        <td>
                                            <?php
                                            $colors = [
                                                'Scheduled' => 'pastel-orange',
                                                'Cancelled' => 'pastel-pink',
                                                'Completed' => 'pastel-blue'
                                            ];
                                            $badgeClass = $colors[$appt['status_name']] ?? 'pastel-gray';
                                            ?>
                                            <span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars($appt['status_name']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick='viewAppointmentDetails(<?= json_encode($appt); ?>)'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- JS Pagination container -->
                        <?php if (count($history_appointments) > 10): ?>
                        <nav aria-label="History appointments pagination">
                            <ul class="pagination justify-content-center mt-3" id="history-pagination">
                                <!-- Pagination will be generated by JS -->
                            </ul>
                        </nav>
                        <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-secondary text-center shadow-sm rounded-3">
                                No past appointments.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateAppointmentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="updateAppointmentForm" method="POST">
      <input type="hidden" name="action" value="update_appointment">
      <input type="hidden" name="appt_id" id="update_appt_id">
      <div class="modal-content">
        <div class="modal-header bg-teal text-white">
          <h5 class="modal-title">Update Appointment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Service</label>
            <select class="form-select" name="serv_id" id="update_service" required>
              <option value="" disabled selected>Select Service</option>
              <?php
              if (!empty($services['data'])) {
                  foreach ($services['data'] as $serv) {
                      echo "<option value='{$serv['serv_id']}'>{$serv['serv_name']}</option>";
                  }
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Doctor</label>
            <select class="form-select" name="doc_id" id="update_doctor" required>
              <option value="" disabled selected>Select Doctor</option>
              <?php
              if (!empty($doctors['data'])) {
                  foreach ($doctors['data'] as $doc) {
                      echo "<option value='{$doc['doc_id']}'>Dr. {$doc['doc_first_name']} {$doc['doc_last_name']}</option>";
                  }
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="appt_date" id="update_date" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Time</label>
            <input type="time" class="form-control" name="appt_time" id="update_time" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Update Appointment</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-teal text-white">
        <h5 class="modal-title">Appointment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="../public/js/management.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePagination('upcoming-appointments-table', 'upcoming-pagination', 10);
    initializePagination('history-appointments-table', 'history-pagination', 10);

    // Reinitialize when switching tabs
    const tabButtons = document.querySelectorAll('#appointmentTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            const target = event.target.getAttribute('data-bs-target');
            setTimeout(() => {
                switch(target) {
                    case '#upcoming':
                        initializePagination('upcoming-appointments-table', 'upcoming-pagination', 10);
                        break;
                    case '#history':
                        initializePagination('history-appointments-table', 'history-pagination', 10);
                        break;
                }
            }, 100);
        });
    });
});

function openUpdateModal(id, service, doctor, date, time) {
    document.getElementById('update_appt_id').value = id;

    let s = document.getElementById('update_service');
    for (let i=0;i<s.options.length;i++){ if (s.options[i].text === service){ s.selectedIndex = i; break; } }

    let d = document.getElementById('update_doctor');
    for (let i=0;i<d.options.length;i++){ if (d.options[i].text === doctor){ d.selectedIndex = i; break; } }

    document.getElementById('update_date').value = date;
    document.getElementById('update_time').value = time;

    new bootstrap.Modal(document.getElementById('updateAppointmentModal')).show();
}

function viewAppointmentDetails(appt) {
    let body = `
        <p><strong>Doctor:</strong> ${appt.doctor_name}</p>
        <p><strong>Service:</strong> ${appt.serv_name}</p>
        <p><strong>Date:</strong> ${appt.appt_date}</p>
        <p><strong>Time:</strong> ${appt.appt_time}</p>
        <p><strong>Status:</strong> ${appt.status_name}</p>
    `;
    document.getElementById('viewModalBody').innerHTML = body;
    new bootstrap.Modal(document.getElementById('viewAppointmentModal')).show();
}

function confirmCancel(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to cancel this appointment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!'
    }).then((res) => {
        if (res.isConfirmed) {
            let f = document.createElement('form');
            f.method = 'POST';
            f.innerHTML = `<input type="hidden" name="action" value="cancel_appointment">
                           <input type="hidden" name="appt_id" value="${id}">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
