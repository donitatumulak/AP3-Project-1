<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/users/Doctor.php';
require_once '../classes/users/Patient.php';
require_once '../classes/users/Staff.php';

$database = new Database();
$db = $database->connect();

$user_type = $_GET['type'] ?? '';
$user_id = $_GET['id'] ?? null;

if ($user_type === 'doctor') {
    $doctor = new Doctor($db);
    
    if ($user_id) {
        // Edit mode - get existing data
        $doctor_data = $doctor->getDoctorById($user_id);
        $data = $doctor_data['status'] === 'success' ? $doctor_data['data'] : [];
    }
    ?>
    <form method="POST" action="user_management.php">
        <input type="hidden" name="action" value="<?php echo $user_id ? 'update_doctor' : 'add_doctor'; ?>">
        <?php if ($user_id): ?>
            <input type="hidden" name="doc_id" value="<?php echo $user_id; ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">First Name *</label>
                    <input type="text" class="form-control" name="first_name" 
                           value="<?php echo $data['doc_first_name'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Last Name *</label>
                    <input type="text" class="form-control" name="last_name" 
                           value="<?php echo $data['doc_last_name'] ?? ''; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Middle Initial</label>
                    <input type="text" class="form-control" name="middle_init" maxlength="1"
                           value="<?php echo $data['doc_middle_init'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" class="form-control" name="contact_num"
                           value="<?php echo $data['doc_contact_num'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Specialty ID *</label>
                    <input type="number" class="form-control" name="spec_id" 
                           value="<?php echo $data['spec_id'] ?? ''; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email"
                   value="<?php echo $data['doc_email'] ?? ''; ?>">
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-teal">
                <?php echo $user_id ? 'Update' : 'Add'; ?> Doctor
            </button>
        </div>
    </form>
    <?php
} elseif ($user_type === 'patient') {
    // Similar structure for patient form
    
    echo "<p>Patient form would go here</p>";
} elseif ($user_type === 'staff') {
    // Similar structure for staff form  
    echo "<p>Staff form would go here</p>";
} else {
    echo "<div class='alert alert-danger'>Invalid user type</div>";
}
?>