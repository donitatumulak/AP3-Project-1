<!-- Add Medical Record Modal -->
<div class="modal fade" id="addMedicalRecordModal" tabindex="-1" aria-labelledby="addMedicalRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="addMedicalRecordModalLabel">Add New Medical Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="medical_records_management.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_medical_record">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="appt_id" class="form-label">Appointment ID *</label>
                            <input type="number" class="form-control" id="appt_id" name="appt_id" required>
                            <small class="form-text text-muted">
                                ⚠️ Enter the raw appointment ID (numeric only), 
                                not the formatted version (e.g. use <code>12</code> instead of <code>2025-10-0000012</code>).
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="med_rec_visit_date" class="form-label">Visit Date *</label>
                            <input type="date" class="form-control" id="med_rec_visit_date" name="med_rec_visit_date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="med_rec_diagnosis" class="form-label">Diagnosis *</label>
                        <textarea class="form-control" id="med_rec_diagnosis" name="med_rec_diagnosis" rows="3" required placeholder="Enter diagnosis details..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="med_rec_prescription" class="form-label">Prescription *</label>
                        <textarea class="form-control" id="med_rec_prescription" name="med_rec_prescription" rows="3" required placeholder="Enter prescription details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Medical Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Medical Record Modal -->
<div class="modal fade" id="editMedicalRecordModal" tabindex="-1" aria-labelledby="editMedicalRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="editMedicalRecordModalLabel">Edit Medical Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editMedicalRecordModalBody">
                <!-- Form will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>