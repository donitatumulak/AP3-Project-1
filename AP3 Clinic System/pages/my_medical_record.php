<?php
$page = 'pages/my_medical_records';
session_start();

require_once '../config/Database.php';
require_once '../classes/medical/MedicalRecord.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_user.php';

// Restrict access to patients only
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'patient') {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. Patients only.</div>";
    require_once '../includes/footer.php';
    exit();
}

$database = new Database();
$db = $database->connect();

// get patient ID from actual login session
$pat_id = $_SESSION['pat_id'] ?? ($_SESSION['profile_id'] ?? null);


if (!$pat_id) {
    echo "<div class='alert alert-warning text-center m-4'>No patient information found in session.</div>";
    require_once '../includes/footer.php';
    exit();
}

$recordObj = new MedicalRecord($db);
$result = $recordObj->getMedicalRecordsByPatientId($pat_id);
$records = $result['data'] ?? [];
?>

<body class="management-page">
<main class="main-content p-4">
  <div class="container">
     <div class="card shadow-sm border-0 rounded-4 mb-4 bg-teal text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">My Medical Records</h3>
                    <p class="mb-0">View your visit history, doctor diagnoses, and prescriptions.</p>
                </div>
                <i class="bi bi-file-medical fa-3x opacity-75"></i>
            </div>
      </div>

    <?php if (empty($records)): ?>
      <div class="alert alert-info text-center shadow-sm rounded-3">
        You currently have no medical records on file.
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($records as $record): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="fw-semibold text-dark mb-2">
                  <?= htmlspecialchars($record['med_rec_diagnosis']) ?>
                </h5>
                <p class="mb-1">
                  <strong>Date:</strong> <?= htmlspecialchars($record['med_rec_visit_date']) ?>
                </p>
                <p class="mb-1">
                  <strong>Doctor:</strong> <?= htmlspecialchars($record['doctor_name']) ?>
                </p>
                <p class="text-muted small flex-grow-1 mt-2">
                  <?= htmlspecialchars($record['med_rec_prescription']) ?>
                </p>
                <button class="btn btn-outline-teal btn-sm mt-auto w-100" 
                        data-bs-toggle="modal" 
                        data-bs-target="#recordModal<?= $record['med_rec_id'] ?>">
                  <i class="bi bi-eye"></i> View Details
                </button>
              </div>
            </div>
          </div>

          <!-- Modal for record details -->
          <div class="modal fade" id="recordModal<?= $record['med_rec_id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content rounded-4">
                <div class="modal-header bg-teal text-white">
                  <h5 class="modal-title">Record Details</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p><strong>Date:</strong> <?= htmlspecialchars($record['med_rec_visit_date']) ?></p>
                  <p><strong>Doctor:</strong> <?= htmlspecialchars($record['doctor_name']) ?></p>
                  <p><strong>Diagnosis:</strong> <?= htmlspecialchars($record['med_rec_diagnosis']) ?></p>
                  <p><strong>Prescription:</strong> <?= htmlspecialchars($record['med_rec_prescription']) ?></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>

<!-- Inline Styles -->
<style>
.management-page .card {
  transition: transform 0.2s ease;
}
.management-page .card:hover {
  transform: translateY(-3px);
}
.btn-outline-teal {
  color: var(--main-teal);
  border-color: var(--main-teal);
}
.btn-outline-teal:hover {
  background-color: var(--main-teal);
  color: #fff;
}
.modal-header.bg-teal {
  background-color: var(--main-teal);
}
</style>
