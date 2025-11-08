<?php
session_start();
$user_type = $_SESSION['user_type'] ?? '';
$is_staff = ($user_type === 'staff');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Allow access to: superadmin and staff (view-only)
$allowed_users = ['superadmin','staff'];
if (!in_array($user_type, $allowed_users)) {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. You don't have permission to view this page.</div>";
    require_once '../includes/footer.php';
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->connect();

require_once '../classes/medical/MedicalRecord.php';
require_once '../classes/appointments/Appointment.php';

// Initialize medical record class
$medicalRecord = new MedicalRecord($db);
$appointment = new Appointment($db);

// Handle form submissions (restricted for staff)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_staff) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_medical_record':
            $result = $medicalRecord->addMedicalRecord(
                $_POST['appt_id'],
                $_POST['med_rec_diagnosis'],
                $_POST['med_rec_prescription'],
                $_POST['med_rec_visit_date']
            );
            break;
            
        case 'update_medical_record':
            $result = $medicalRecord->updateMedicalRecord(
                $_POST['med_rec_id'],
                $_POST['appt_id'],
                $_POST['med_rec_diagnosis'],
                $_POST['med_rec_prescription'],
                $_POST['med_rec_visit_date']
            );
            break;
            
        case 'delete_medical_record':
            $result = $medicalRecord->deleteMedicalRecord($_POST['med_rec_id']);
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

// Get all medical records for display
$medical_records = $medicalRecord->getAllMedicalRecords();

$page = 'pages/medical_records_management';
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
                            <h1><i class="fas fa-file-medical me-3"></i>Medical Records Management</h1>
                            <p class="text-muted mb-0">
                                <?php echo $is_staff ? 'View patient medical records' : 'Manage patient medical records, diagnoses, and prescriptions'; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card">
                                <div class="stats-content">
                                    <i class="fas fa-file-medical-alt stats-icon"></i>
                                    <div class="stats-text">
                                        <div class="stats-number">
                                            <?php echo $medical_records['status'] === 'success' ? count($medical_records['data']) : 0; ?>
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

                <!-- Medical Records Table -->
                <div class="management-table-container">
                    <!-- Table Header with Search and Actions -->
                    <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                        <div class="search-section">
                            <div class="input-group search-box">
                                <input type="text" class="form-control" 
                                       placeholder="Search medical records..." 
                                       id="search-medical-records">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="action-section">
                            <?php if (!$is_staff): ?>
                                <button class="btn btn-teal" onclick="openAddModal()">
                                    <i class="fas fa-plus"></i> Add Medical Record
                                </button>
                            <?php else: ?>
                                <span class="text-muted">
                                    <i class="fas fa-eye me-1"></i>View Only Access
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Medical Records Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="medical-records-table">
                            <thead class="table-teal">
                                <tr>
                                    <th width="8%">Record ID</th>
                                    <th width="12%">Appointment ID</th>
                                    <th width="16%">Patient Name</th>
                                    <th width="16%">Doctor</th>
                                    <th width="16%">Diagnosis</th>
                                    <th width="16%">Prescription</th>
                                    <th width="12%">Visit Date</th>
                                    <th width="8%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($medical_records['status'] === 'success' && !empty($medical_records['data'])): ?>
                                    <?php foreach ($medical_records['data'] as $record): ?>
                                    <tr>
                                        <td><?php echo $record['med_rec_id']; ?></td>
                                        <td>
                                            <?php $formattedApptId = $appointment->formatAppointmentId($record['appt_id'], $record['appt_date']);?>
                                          <span class="badge pastel-green"><?php echo $formattedApptId; ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($record['patient_name']); ?></strong>
                                            <small class="text-muted d-block">PAT-<?php echo $record['patient_id']; ?></small>
                                        </td>
                                        <td>
                                            <span><?php echo htmlspecialchars($record['doctor_name']); ?></span>
                                            <small class="text-muted d-block">DOC-<?php echo $record['doctor_id']; ?></small>
                                        </td>
                                        <td>
                                            <span class="diagnosis-preview" 
                                                  data-bs-toggle="tooltip" 
                                                  title="<?php echo htmlspecialchars($record['med_rec_diagnosis']); ?>">
                                                <?php 
                                                $diagnosis = $record['med_rec_diagnosis'];
                                                echo strlen($diagnosis) > 50 ? htmlspecialchars(substr($diagnosis, 0, 50)) . '...' : htmlspecialchars($diagnosis);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="prescription-preview" 
                                                  data-bs-toggle="tooltip" 
                                                  title="<?php echo htmlspecialchars($record['med_rec_prescription']); ?>">
                                                <?php 
                                                $prescription = $record['med_rec_prescription'];
                                                echo strlen($prescription) > 50 ? htmlspecialchars(substr($prescription, 0, 50)) . '...' : htmlspecialchars($prescription);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($record['med_rec_visit_date'])); ?>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-outline-info btn-action" 
                                                        onclick="viewRecordDetails(<?php echo $record['med_rec_id']; ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (!$is_staff): ?>
                                                    <button class="btn btn-outline-warning btn-action" 
                                                            onclick="openEditModal(<?php echo $record['med_rec_id']; ?>)"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-action"
                                                            onclick="confirmDelete(<?php echo $record['med_rec_id']; ?>, 'Record #<?php echo $record['med_rec_id']; ?>', 'medical_record', 'medical_records_management.php')"
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
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-file-medical fa-2x mb-2 d-block"></i>
                                            <?php echo $medical_records['status'] === 'error' ? 'Error: ' . htmlspecialchars($medical_records['message']) : 'No medical records found.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($medical_records['status'] === 'success' && !empty($medical_records['data']) && count($medical_records['data']) > 10): ?>
                    <nav aria-label="Medical records pagination">
                        <ul class="pagination justify-content-center mt-3" id="medical-records-pagination">
                            <!-- Pagination will be generated by JavaScript -->
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Reusable Modals -->
    <?php include '../components/management_modals.php'; ?>

    <!-- Medical Record Specific Modals -->
    <?php if (!$is_staff): ?>
        <?php
        // Add Medical Record Modal (only for non-staff)
        renderAddModal('addMedicalRecordModal', 'Medical Record', 'medical_records_management.php', 'medical_record', [
            ['name' => 'appt_id', 'label' => 'Appointment ID', 'type' => 'number', 'required' => true, 'width' => 'col-md-6', 'help' => '⚠️ Enter the raw appointment ID (numeric only), not the formatted version'],
            ['name' => 'med_rec_visit_date', 'label' => 'Visit Date', 'type' => 'date', 'required' => true, 'width' => 'col-md-6'],
            ['name' => 'med_rec_diagnosis', 'label' => 'Diagnosis', 'type' => 'textarea', 'required' => true, 'rows' => 3, 'placeholder' => 'Enter diagnosis details...'],
            ['name' => 'med_rec_prescription', 'label' => 'Prescription', 'type' => 'textarea', 'required' => true, 'rows' => 3, 'placeholder' => 'Enter prescription details...']
        ]);

        // Edit Medical Record Modal (only for non-staff)
        renderEditModal('editMedicalRecordModal', 'Medical Record', 'medical_record');
        ?>
    <?php endif; ?>

    <!-- SweetAlert + JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/management.js"></script>

    <script>
    // Medical Record Specific Functions
    function openAddModal() {
        <?php if (!$is_staff): ?>
            const modal = new bootstrap.Modal(document.getElementById('addMedicalRecordModal'));
            modal.show();
        <?php endif; ?>
    }

    function openEditModal(recordId) {
        <?php if (!$is_staff): ?>
            const modal = new bootstrap.Modal(document.getElementById('editMedicalRecordModal'));
            // Load record data via AJAX
            fetch(`../handlers/medical/get_medical_record.php?id=${recordId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('editMedicalRecordModalBody').innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading form:', error);
                    Swal.fire('Error!', 'Failed to load medical record form', 'error');
                });
        <?php endif; ?>
    }

    function viewRecordDetails(recordId) {
        const customLabels = {
            'formatted_appt_id': 'Appointment',
            'patient_name': 'Patient',
            'doctor_name': 'Doctor', 
            'med_rec_visit_date': 'Visit Date',
            'med_rec_diagnosis': 'Diagnosis',
            'med_rec_prescription': 'Prescription'
        };
        
        // ONLY show these specific fields (in this order)
        const allowedFields = [
            'formatted_appt_id',
            'patient_name', 
            'doctor_name',
            'med_rec_visit_date',
            'med_rec_diagnosis',
            'med_rec_prescription'
        ];
        
        viewItemDetails(recordId, 'medical_record', '../handlers/medical/get_medical_record_details.php', customLabels, allowedFields);
    }

    // Initialize management features for medical records
    document.addEventListener('DOMContentLoaded', function() {
        initializeManagementFeatures({
            searchInputId: 'search-medical-records',
            tableId: 'medical-records-table',
            paginationId: 'medical-records-pagination',
            itemsPerPage: 10
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>