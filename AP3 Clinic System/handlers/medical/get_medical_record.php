<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/medical/MedicalRecord.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $record_id = $_GET['id'];

    if (!is_numeric($record_id)) {
        echo '<div class="p-3 text-danger text-center">Invalid medical record ID.</div>';
        exit;
    }

    try {
        $database = new Database();
        $db = $database->connect();
        $medicalRecord = new MedicalRecord($db);
        $result = $medicalRecord->getMedicalRecordById($record_id);

        if ($result['status'] === 'success' && !empty($result['data'])) {
            $record = $result['data'];
            ?>
            <form method="POST" action="medical_records_management.php">
                <input type="hidden" name="action" value="update_medical_record">
                <input type="hidden" name="med_rec_id" value="<?= htmlspecialchars($record['med_rec_id']) ?>">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Appointment ID *</label>
                            <input type="number" class="form-control" name="appt_id" 
                                value="<?= htmlspecialchars($record['appt_id']) ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visit Date *</label>
                            <input type="date" class="form-control" name="med_rec_visit_date" 
                                value="<?= htmlspecialchars($record['med_rec_visit_date']) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diagnosis *</label>
                        <textarea class="form-control" name="med_rec_diagnosis" rows="3" required><?= htmlspecialchars($record['med_rec_diagnosis']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prescription *</label>
                        <textarea class="form-control" name="med_rec_prescription" rows="3" required><?= htmlspecialchars($record['med_rec_prescription']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Save Changes</button>
                </div>
            </form>
            <?php
        } else {
            echo '<div class="p-3 text-center text-danger">Record not found.</div>';
        }
    } catch (Exception $e) {
        echo '<div class="p-3 text-center text-danger">Server error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="p-3 text-center text-danger">Invalid request.</div>';
}
?>
