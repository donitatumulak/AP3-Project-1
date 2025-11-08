<?php
session_start();
$user_type = $_SESSION['user_type'] ?? '';
$is_staff = ($user_type === 'staff');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Allow access to: superadmin and staff
$allowed_users = ['superadmin', 'staff'];
if (!in_array($user_type, $allowed_users)) {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. You don't have permission to view this page.</div>";
    require_once '../includes/footer.php';
    exit();
}

// Set default tab based on user type
$default_tab = $is_staff ? 'statuses' : 'appointments';

require_once '../config/Database.php';
$database = new Database();
$db = $database->connect();

require_once '../classes/appointments/Appointment.php';
require_once '../classes/appointments/Schedule.php';
require_once '../classes/appointments/Status.php';

// Initialize classes
$appointment = new Appointment($db);
$schedule = new Schedule($db);
$status = new Status($db);

// Get dropdown data for add modal (only if not staff)
if (!$is_staff) {
    $patients_data = $appointment->getAllPatients();
    $doctors_data = $appointment->getAllDoctors();
    $services_data = $appointment->getAllServices();
    $statuses_data = $appointment->getAllStatuses();
    $schedule_doctors_data = $doctors_data; 
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        // Staff can only handle status actions
        case 'add_status':
            $result = $status->addStatus($_POST['stat_name']);
            break;
            
        case 'update_status':
            $result = $status->updateStatus(
                $_POST['stat_id'],
                $_POST['stat_name']
            );
            break;
            
        // Non-staff can handle all actions
        case 'add_appointment':
            if (!$is_staff) {
                $result = $appointment->addAppointment(
                    $_POST['appt_date'],
                    $_POST['appt_time'],
                    $_POST['pat_id'],
                    $_POST['doc_id'],
                    $_POST['serv_id'],
                    $_POST['stat_id']
                );
            }
            break;
            
        case 'update_appointment':
            if (!$is_staff) {
                $result = $appointment->updateAppointment(
                    $_POST['appt_id'],
                    $_POST['appt_time'],
                    $_POST['appt_date'],
                    $_POST['doc_id'],
                    $_POST['serv_id'],
                    $_POST['pat_id'],
                    $_POST['stat_id']
                );
            }
            break;
            
        case 'cancel_appointment':
            if (!$is_staff) {
                $result = $appointment->cancelAppointment($_POST['appt_id']);
            }
            break;
            
        case 'update_appointment_status':
            if (!$is_staff) {
                $result = $appointment->updateAppointmentStatus(
                    $_POST['appt_id'],
                    $_POST['stat_id']
                );
            }
            break;
            
        case 'add_schedule':
            if (!$is_staff) {
                $result = $schedule->addSchedule(
                    $_POST['doc_id'],
                    $_POST['sched_days'],
                    $_POST['sched_start_time'],
                    $_POST['sched_end_time']
                );
            }
            break;
            
        case 'update_schedule':
            if (!$is_staff) {
                $result = $schedule->updateSchedule(
                    $_POST['sched_id'],
                    $_POST['doc_id'],
                    $_POST['sched_days'],
                    $_POST['sched_start_time'],
                    $_POST['sched_end_time']
                );
            }
            break;
            
        case 'delete_schedule':
            if (!$is_staff) {
                $result = $schedule->deleteSchedule($_POST['sched_id']);
            }
            break;
            
        case 'delete_status':
            if (!$is_staff) {
                $result = $status->deleteStatus($_POST['stat_id']);
            }
            break;
    }
    
    if (isset($result)) {
        $_SESSION['message'] = [
            'type' => $result['status'],
            'text' => $result['message']
        ];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Get all data for display
if (!$is_staff) {
    $appointments = $appointment->getRecentAppointments();
    $schedules = $schedule->getAllSchedules();
    $today_schedules = $schedule->getTodaySchedules();
}
$statuses = $status->getAllStatuses();

$page = 'pages/appointment_management';
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
                            <h1><i class="fas fa-calendar-alt me-3"></i>Appointment Management</h1>
                            <p class="text-muted mb-0">
                                <?php echo $is_staff ? 'Manage status types' : 'Manage appointments, doctor schedules, and status types'; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card">
                                <div class="stats-content">
                                    <i class="fas fa-calendar-check stats-icon"></i>
                                    <div class="stats-text">
                                        <div class="stats-number">
                                            <?php 
                                            $total = 0;
                                            if (!$is_staff) {
                                                if (!empty($appointments)) $total += count($appointments);
                                                if ($schedules['status'] === 'success') $total += count($schedules['data']);
                                            }
                                            if ($statuses['status'] === 'success') $total += count($statuses['data']);
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

                <!-- Appointment Management Tabs -->
                <div class="management-tabs">
                    <nav>
                        <div class="nav nav-tabs" id="appointmentTabs" role="tablist">
                            <?php if (!$is_staff): ?>
                                <!-- Show these tabs only for non-staff -->
                                <button class="nav-link active" id="appointments-tab" data-bs-toggle="tab" 
                                        data-bs-target="#appointments" type="button" role="tab">
                                    <i class="fas fa-calendar-alt"></i>
                                    Appointments
                                    <span class="badge bg-primary ms-1">
                                        <?php echo !empty($appointments) ? count($appointments) : 0; ?>
                                    </span>
                                </button>
                                <button class="nav-link" id="doctor-appts-tab" data-bs-toggle="tab" 
                                        data-bs-target="#doctor-appts" type="button" role="tab">
                                    <i class="fas fa-user-md"></i>
                                    Doctor's Appointments
                                </button>
                                <button class="nav-link" id="schedules-tab" data-bs-toggle="tab" 
                                        data-bs-target="#schedules" type="button" role="tab">
                                    <i class="fas fa-clock"></i>
                                    Schedules
                                    <span class="badge bg-success ms-1">
                                        <?php echo $schedules['status'] === 'success' ? count($schedules['data']) : 0; ?>
                                    </span>
                                </button>
                            <?php endif; ?>

                            <!-- Status tab - always visible, active for staff -->
                            <button class="nav-link <?php echo $is_staff ? 'active' : ''; ?>" id="statuses-tab" data-bs-toggle="tab" 
                                    data-bs-target="#statuses" type="button" role="tab">
                                <i class="fas fa-tags"></i>
                                Status Types
                                <span class="badge bg-info ms-1">
                                    <?php echo $statuses['status'] === 'success' ? count($statuses['data']) : 0; ?>
                                </span>
                            </button>
                        </div>
                    </nav>

                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white">
                        <?php if (!$is_staff): ?>
                            <!-- Appointments Tab - Only for non-staff -->
                            <div class="tab-pane fade show active" id="appointments" role="tabpanel">
                                <div class="management-table-container">
                                    <!-- Table Header with Search and Actions -->
                                    <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                        <div class="search-section">
                                            <div class="input-group search-box">
                                                <input type="text" class="form-control" 
                                                       placeholder="Search appointments..." 
                                                       id="search-appointments">
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="action-section">
                                            <button class="btn btn-teal" onclick="openAddAppointmentModal()">
                                                <i class="fas fa-plus"></i> Add Appointment
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Appointments Table -->
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="appointments-table">
                                            <thead class="table-teal">
                                                <tr>
                                                    <th>Appointment ID</th>
                                                    <th>Patient</th>
                                                    <th>Doctor</th>
                                                    <th>Date & Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($appointments)): ?>
                                                    <?php foreach ($appointments as $appt): ?>
                                                    <tr>
                                                        <td>
                                                            <strong class="badge pastel-green"><?php echo $appointment->formatAppointmentId($appt['appt_id'], $appt['appt_date']); ?></strong>
                                                            <small class="text-muted d-block">ID: <?php echo $appt['appt_id']; ?></small>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($appt['pat_first_name'] . ' ' . $appt['pat_last_name']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <span>Dr. <?php echo htmlspecialchars($appt['doc_first_name'] . ' ' . $appt['doc_last_name']); ?></span>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo date('M j, Y', strtotime($appt['appt_date'])); ?></strong>
                                                            <small class="text-muted d-block"><?php echo date('g:i A', strtotime($appt['appt_time'])); ?></small>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $status_class = 'bg-secondary';
                                                            if ($appt['stat_name'] === 'Scheduled') $status_class = 'pastel-orange';
                                                            elseif ($appt['stat_name'] === 'Cancelled') $status_class = 'pastel-pink';
                                                            elseif ($appt['stat_name'] === 'Completed') $status_class = 'pastel-blue';
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>">
                                                                <?php echo htmlspecialchars($appt['stat_name']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <div class="d-flex gap-1">
                                                                <button class="btn btn-outline-warning btn-action" 
                                                                        onclick="openEditAppointmentModal(<?php echo $appt['appt_id']; ?>)"
                                                                        title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-primary btn-action"
                                                                        onclick="openUpdateStatusModal(<?php echo $appt['appt_id']; ?>)"
                                                                        title="Update Status">
                                                                    <i class="fas fa-sync"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-action"
                                                                        onclick="cancelAppointment(<?php echo $appt['appt_id']; ?>, '<?php echo $appointment->formatAppointmentId($appt['appt_id'], $appt['appt_date']); ?>')"
                                                                        title="Cancel">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                                                            No appointments found.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination for Appointments -->
                                    <?php if (!empty($appointments) && count($appointments) > 10): ?>
                                    <nav aria-label="Appointments pagination">
                                        <ul class="pagination justify-content-center mt-3" id="appointments-pagination">
                                            <!-- Pagination will be generated by JavaScript -->
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Doctor's Appointments Tab - Only for non-staff -->
                            <div class="tab-pane fade" id="doctor-appts" role="tabpanel">
                                <div class="management-table-container">
                                    <!-- Search Section Only (no table initially) -->
                                    <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                        <div class="search-section w-100 text-center">
                                            <div class="row justify-content-center">
                                                <div class="col-md-8">
                                                    <h5 class="text-teal mb-3">
                                                        <i class="fas fa-user-md me-2"></i>Search Doctor's Appointments
                                                    </h5>
                                                    <div class="input-group search-box" style = "width: 700px; margin: 0 auto;">
                                                        <input type="text" class="form-control" 
                                                            placeholder="Enter doctor's name to view their appointments..." 
                                                            id="search-doctor-appointments" style="width: 500px;">
                                                        <button class="btn btn-teal" type="button" onclick="searchDoctorAppointments()">
                                                            <i class="fas fa-search"></i> Search Doctor
                                                        </button>
                                                    </div>
                                                    <small class="text-muted mt-2 d-block">
                                                        Search by doctor name to view their recent, past, and future appointments
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Time Filters (hidden by default) -->
                                    <div class="time-filters-section mb-3 text-center" id="timeFiltersSection" style="display: none;">
                                        <h6 class="text-muted mb-2">Filter Appointments</h6>
                                        <div class="btn-group" role="group" aria-label="Appointment time filter">
                                            <input type="radio" class="btn-check" name="apptTimeFilter" id="filter-all-appts" autocomplete="off" checked>
                                            <label class="btn btn-outline-teal" for="filter-all-appts">
                                                <i class="fas fa-calendar-alt me-1"></i>All Appointments
                                            </label>

                                            <input type="radio" class="btn-check" name="apptTimeFilter" id="filter-today-appts" autocomplete="off">
                                            <label class="btn btn-outline-teal" for="filter-today-appts">
                                                <i class="fas fa-calendar-day me-1"></i>Today's
                                            </label>

                                            <input type="radio" class="btn-check" name="apptTimeFilter" id="filter-future-appts" autocomplete="off">
                                            <label class="btn btn-outline-teal" for="filter-future-appts">
                                                <i class="fas fa-calendar-plus me-1"></i>Future
                                            </label>

                                            <input type="radio" class="btn-check" name="apptTimeFilter" id="filter-past-appts" autocomplete="off">
                                            <label class="btn btn-outline-teal" for="filter-past-appts">
                                                <i class="fas fa-history me-1"></i>Previous
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Initial Instructions -->
                                    <div class="text-center py-5" id="doctorAppointmentsInstructions">
                                        <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Search for a Doctor</h5>
                                        <p class="text-muted">Enter a doctor's name above to view their appointment history</p>
                                    </div>

                                    <!-- Results Table (hidden by default) -->
                                    <div class="table-responsive" id="doctorAppointmentsResults" style="display: none;">
                                        <table class="table table-hover" id="doctor-appointments-table">
                                            <thead class="table-teal">
                                                <tr>
                                                    <th>Appointment ID</th>
                                                    <th>Patient</th>
                                                    <th>Doctor</th>
                                                    <th>Service</th>
                                                    <th>Date & Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="doctor-appointments-tbody">
                                                <!-- Table rows will be populated via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination for Doctor Appointments -->
                                    <nav aria-label="Doctor appointments pagination" id="doctorAppointmentsPaginationContainer" style="display: none;">
                                        <ul class="pagination justify-content-center mt-3" id="doctor-appointments-pagination">
                                            <!-- Pagination will be generated by JavaScript -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <!-- Schedules Tab - Only for non-staff -->
                            <div class="tab-pane fade" id="schedules" role="tabpanel">
                                <div class="management-table-container">
                                    <!-- Today's Schedules Alert -->
                                    <?php if ($today_schedules['status'] === 'success' && !empty($today_schedules['data'])): ?>
                                    <div class="alert alert-info mb-3">
                                        <h6><i class="fas fa-calendar-day me-2"></i>Today's Schedules (<?php echo date('l'); ?>)</h6>
                                        <div class="row mt-2">
                                            <?php foreach ($today_schedules['data'] as $today_sched): ?>
                                            <div class="col-md-6 mb-2">
                                                <small>
                                                    <strong><?php echo htmlspecialchars($today_sched['doctor_name']); ?></strong>: 
                                                    <?php echo date('g:i A', strtotime($today_sched['sched_start_time'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($today_sched['sched_end_time'])); ?>
                                                </small>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Table Header with Search and Actions -->
                                    <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                        <div class="search-section">
                                            <div class="input-group search-box">
                                                <input type="text" class="form-control" 
                                                       placeholder="Search schedules..." 
                                                       id="search-schedules">
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="action-section">
                                            <button class="btn btn-teal" onclick="openAddScheduleModal()">
                                                <i class="fas fa-plus"></i> Add Schedule
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Schedules Table -->
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="schedules-table">
                                            <thead class="table-teal">
                                                <tr>
                                                    <th>Schedule ID</th>
                                                    <th>Doctor</th>
                                                    <th>Day</th>
                                                    <th>Start Time</th>
                                                    <th>End Time</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($schedules['status'] === 'success' && !empty($schedules['data'])): ?>
                                                    <?php foreach ($schedules['data'] as $sched): ?>
                                                    <tr>
                                                        <td><?php echo $sched['sched_id']; ?></td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($sched['doctor_name']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge pastel-blue"><?php echo htmlspecialchars($sched['sched_days']); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php echo date('g:i A', strtotime($sched['sched_start_time'])); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo date('g:i A', strtotime($sched['sched_end_time'])); ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <div class="d-flex gap-1">
                                                                <button class="btn btn-outline-warning btn-action" 
                                                                        onclick="openEditScheduleModal(<?php echo $sched['sched_id']; ?>)"
                                                                        title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-action"
                                                                        onclick="deleteSchedule(<?php echo $sched['sched_id']; ?>, '<?php echo htmlspecialchars($sched['doctor_name']); ?> - <?php echo htmlspecialchars($sched['sched_days']); ?>')"
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
                                                            <i class="fas fa-clock fa-2x mb-2 d-block"></i>
                                                            <?php echo $schedules['status'] === 'error' ? 'Error: ' . htmlspecialchars($schedules['message']) : 'No schedules found.'; ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination for Schedules -->
                                    <?php if ($schedules['status'] === 'success' && !empty($schedules['data']) && count($schedules['data']) > 10): ?>
                                    <nav aria-label="Schedules pagination">
                                        <ul class="pagination justify-content-center mt-3" id="schedules-pagination">
                                            <!-- Pagination will be generated by JavaScript -->
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Statuses Tab - Always visible, active for staff -->
                        <div class="tab-pane fade <?php echo $is_staff ? 'show active' : ''; ?>" id="statuses" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search statuses..." 
                                                   id="search-statuses">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddStatusModal()">
                                            <i class="fas fa-plus"></i> Add Status
                                        </button>
                                    </div>
                                </div>

                                <!-- Statuses Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover" id="statuses-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th width="20%" class="text-center">Status ID</th>
                                                <th width="60%" class="text-center">Status Name</th>
                                                <th width="20%" class="text-start ps-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($statuses['status'] === 'success' && !empty($statuses['data'])): ?>
                                                <?php foreach ($statuses['data'] as $stat): ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?php echo $stat['stat_id']; ?></td>
                                                    <td class="text-center align-middle">
                                                        <h6 class="mb-0 text-teal"><?php echo htmlspecialchars($stat['stat_name']); ?></h6>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-warning btn-action" 
                                                                    onclick="openEditStatusModal(<?php echo $stat['stat_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if (!$is_staff): ?>
                                                            <button class="btn btn-outline-danger btn-action"
                                                                    onclick="deleteStatus(<?php echo $stat['stat_id']; ?>, '<?php echo htmlspecialchars($stat['stat_name']); ?>')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">
                                                        <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                                        <?php echo $statuses['status'] === 'error' ? 'Error: ' . htmlspecialchars($statuses['message']) : 'No statuses found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination for Statuses -->
                                <?php if ($statuses['status'] === 'success' && !empty($statuses['data']) && count($statuses['data']) > 10): ?>
                                <nav aria-label="Statuses pagination">
                                    <ul class="pagination justify-content-center mt-3" id="statuses-pagination">
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
    <?php include '../components/appointment_modals.php'; ?>

    <!-- SweetAlert + JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/management.js"></script>

   <script>
    // Debug function
    function debugLog(message) {
        console.log('DEBUG:', message);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Only initialize search and pagination for visible tables
        <?php if (!$is_staff): ?>
            initializeSearch('search-appointments', 'appointments-table');
            initializeSearch('search-schedules', 'schedules-table');
            initializeSearch('search-statuses', 'statuses-table');
            initializeSearch('search-doctor-appointments', 'doctor-appointments-table');
            
            initializePagination('appointments-table', 'appointments-pagination', 10);
            initializePagination('schedules-table', 'schedules-pagination', 10);
            initializePagination('statuses-table', 'statuses-pagination', 10);
            initializePagination('doctor-appointments-table', 'doctor-appointments-pagination', 10);
            
            initializeDoctorAppointmentFilters();
            initializeDoctorAppointmentsSearchReset();
            
            // Reinitialize pagination when switching tabs
            const tabButtons = document.querySelectorAll('#appointmentTabs button[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('data-bs-target');
                    setTimeout(() => {
                        switch(target) {
                            case '#appointments':
                                initializePagination('appointments-table', 'appointments-pagination', 10);
                                break;
                            case '#doctor-appts':
                                initializePagination('doctor-appointments-table', 'doctor-appointments-pagination', 10);
                                break;
                            case '#schedules':
                                initializePagination('schedules-table', 'schedules-pagination', 10);
                                break;
                            case '#statuses':
                                initializePagination('statuses-table', 'statuses-pagination', 10);
                                break;
                        }
                    }, 100);
                });
            });
        <?php else: ?>
            // For staff, only initialize status functionality
            initializeSearch('search-statuses', 'statuses-table');
            initializePagination('statuses-table', 'statuses-pagination', 10);
        <?php endif; ?>
    });

    // ========== APPOINTMENT TAB FUNCTIONS ==========
    function openAddAppointmentModal() {
        <?php if (!$is_staff): ?>
            debugLog('Opening add appointment modal');
            const modal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
            modal.show();
        <?php endif; ?>
    }

    function openUpdateStatusModal(appointmentId) {
        <?php if (!$is_staff): ?>
            debugLog('Opening update status modal for appointment: ' + appointmentId);
            
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            fetch(`/AP3%20Clinic%20System/handlers/appointments/get_appointment_form.php?id=${appointmentId}&type=status_update_form`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    document.getElementById('updateStatusModalBody').innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading form:', error);
                    Swal.fire('Error!', 'Failed to load status update form', 'error');
                });
        <?php endif; ?>
    }

    function updateDoctorAppointmentStatus(appointmentId) {
        <?php if (!$is_staff): ?>
            debugLog('Updating doctor appointment status: ' + appointmentId);
            openUpdateStatusModal(appointmentId); // Call the same function
        <?php endif; ?>
    }

    function viewAppointmentDetails(appointmentId) {
        <?php if (!$is_staff): ?>
            debugLog('Viewing appointment details: ' + appointmentId);
            
            // Since there's no details form, create a simple one or show info
            Swal.fire({
                title: 'Appointment Details',
                html: `
                    <div class="text-start">
                        <p><strong>Appointment ID:</strong> ${appointmentId}</p>
                        <p><strong>Note:</strong> Detailed view is not available yet.</p>
                        <p class="text-muted small">You can edit the appointment to see full details.</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Edit Appointment',
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    openEditAppointmentModal(appointmentId);
                }
            });
        <?php endif; ?>
    }

    // Make sure openEditAppointmentModal uses the correct type
    function openEditAppointmentModal(appointmentId) {
        <?php if (!$is_staff): ?>
            debugLog('Opening edit appointment modal for: ' + appointmentId);
            const modal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
            fetch(`/AP3%20Clinic%20System/handlers/appointments/get_appointment_form.php?id=${appointmentId}&type=appointment_form`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    document.getElementById('editAppointmentModalBody').innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading form:', error);
                    Swal.fire('Error!', 'Failed to load appointment form', 'error');
                });
        <?php endif; ?>
    }


    function cancelAppointment(appointmentId, appointmentInfo) {
        <?php if (!$is_staff): ?>
            debugLog('Canceling appointment: ' + appointmentId);
            
            Swal.fire({
                title: 'Cancel Appointment?',
                text: `Are you sure you want to cancel appointment ${appointmentInfo}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form to cancel appointment
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo $_SERVER["PHP_SELF"]; ?>';
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'cancel_appointment';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'appt_id';
                    idInput.value = appointmentId;
                    
                    form.appendChild(actionInput);
                    form.appendChild(idInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        <?php endif; ?>
    }

    // ========== DOCTOR'S APPOINTMENT TAB FUNCTIONS ==========
    function updateDoctorAppointmentStatus(appointmentId) {
        <?php if (!$is_staff): ?>
            debugLog('Updating doctor appointment status: ' + appointmentId);
            openUpdateStatusModal(appointmentId);
        <?php endif; ?>
    }

    function viewAppointmentDetails(appointmentId) {
    const modal = new bootstrap.Modal(document.getElementById('appointmentDetailsModal'));
    const body = document.getElementById('appointmentDetailsModalBody');

    // Show loading message
    body.innerHTML = `<div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                        <p>Loading details...</p>
                      </div>`;
    modal.show();

    // Fetch details
    fetch(`/AP3%20Clinic%20System/handlers/appointments/get_appointment_form.php?id=${appointmentId}&type=appointment_details`)
        .then(response => response.text())
        .then(html => {
            body.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading details:', error);
            body.innerHTML = `<div class="text-center text-danger">Failed to load appointment details.</div>`;
        });
}
    // ========== SCHEDULE TAB FUNCTIONS ==========
    function openAddScheduleModal() {
        <?php if (!$is_staff): ?>
            debugLog('Opening add schedule modal');
            const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
            modal.show();
        <?php endif; ?>
    }

    function openEditScheduleModal(scheduleId) {
        <?php if (!$is_staff): ?>
            debugLog('Opening edit schedule modal for: ' + scheduleId);
            
            const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
            fetch(`/AP3%20Clinic%20System/handlers/appointments/get_appointment_form.php?id=${scheduleId}&type=schedule_form`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    document.getElementById('editScheduleModalBody').innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading form:', error);
                    Swal.fire('Error!', 'Failed to load schedule form', 'error');
                });
        <?php endif; ?>
    }

    function deleteSchedule(scheduleId, scheduleInfo) {
        <?php if (!$is_staff): ?>
            debugLog('Deleting schedule: ' + scheduleId);
            
            Swal.fire({
                title: 'Delete Schedule?',
                text: `Are you sure you want to delete schedule: ${scheduleInfo}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form to delete schedule
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo $_SERVER["PHP_SELF"]; ?>';
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'delete_schedule';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'sched_id';
                    idInput.value = scheduleId;
                    
                    form.appendChild(actionInput);
                    form.appendChild(idInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        <?php endif; ?>
    }

    // ========== STATUSES TAB FUNCTIONS ==========
    function openAddStatusModal() {
        debugLog('Opening add status modal');
        const modal = new bootstrap.Modal(document.getElementById('addStatusModal'));
        modal.show();
    }

    function openEditStatusModal(statusId) {
        debugLog('Opening edit status modal for: ' + statusId);
        
        const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
        fetch(`/AP3%20Clinic%20System/handlers/appointments/get_appointment_form.php?id=${statusId}&type=status_form`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                document.getElementById('editStatusModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load status form', 'error');
            });
    }

    function deleteStatus(statusId, statusName) {
        <?php if (!$is_staff): ?>
            debugLog('Deleting status: ' + statusId);
            
            Swal.fire({
                title: 'Delete Status?',
                text: `Are you sure you want to delete status: ${statusName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form to delete status
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo $_SERVER["PHP_SELF"]; ?>';
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'delete_status';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'stat_id';
                    idInput.value = statusId;
                    
                    form.appendChild(actionInput);
                    form.appendChild(idInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        <?php endif; ?>
    }

</script>

    <?php include '../includes/footer.php'; ?>