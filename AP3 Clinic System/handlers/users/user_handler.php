<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/users/Doctor.php';
require_once '../../classes/users/Patient.php';
require_once '../../classes/users/Staff.php';
require_once '../../classes/users/User.php';

$database = new Database();
$db = $database->connect();

$doctor = new Doctor($db);
$patient = new Patient($db);
$staff = new Staff($db);
$user = new User($db);

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? null;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* --------------------------
   🧾 VIEW DETAILS SECTION
-------------------------- */
if ($type === 'user_details') {
    $record = $user->getUserById($id);
    if ($record['status'] === 'success') {
        $u = $record['data'];
        
        echo '
        <div class="modal-header bg-teal text-white">
            <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>User Account Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">User ID</h6>
                    <p>#' . htmlspecialchars($u['user_id']) . '</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Account Type</h6>';
        
        $type_class = 'bg-secondary';
        if ($u['user_type'] === 'doctor') $type_class = 'bg-primary';
        elseif ($u['user_type'] === 'patient') $type_class = 'bg-success';
        elseif ($u['user_type'] === 'staff') $type_class = 'bg-info';
        elseif ($u['user_type'] === 'superadmin') $type_class = 'bg-danger';
        elseif ($u['user_type'] === 'inactive') $type_class = 'bg-warning';
        
        echo '          <span class="badge ' . $type_class . '">' . ucfirst($u['user_type']) . '</span>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Username</h6>
                    <p>' . htmlspecialchars($u['user_name']) . '</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Super Admin</h6>
                    <p>' . ($u['user_is_superadmin'] ? 'Yes' : 'No') . '</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Last Login</h6>
                    <p>' . ($u['user_last_login'] ? date('F j, Y g:i A', strtotime($u['user_last_login'])) : 'Never logged in') . '</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Account Created</h6>
                    <p>' . date('F j, Y', strtotime($u['user_created_at'])) . '</p>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <h6 class="fw-bold mb-1">Linked Profile IDs</h6>
                    <p class="mb-1">Patient ID: ' . ($u['pat_id'] ?? 'None') . '</p>
                    <p class="mb-1">Doctor ID: ' . ($u['doc_id'] ?? 'None') . '</p>
                    <p class="mb-0">Staff ID: ' . ($u['staff_id'] ?? 'None') . '</p>
                </div>
            </div>
        </div>';
    } else {
        echo '<div class="p-4 text-center text-danger">User not found.</div>';
    }
}

/* --------------------------
   ✏️ DOCTOR FORM SECTION
-------------------------- */
elseif ($type === 'doctor_form') {
    $doctor_data = $id ? $doctor->getDoctorById($id) : ['status' => 'success', 'data' => []];
    $specializations = $doctor->getAllSpecializations(); // Get specializations for dropdown
    
    if ($doctor_data['status'] === 'success') {
        $d = $doctor_data['data'];
        
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/user_management.php">
            <input type="hidden" name="action" value="' . ($id ? 'update_doctor' : 'add_doctor') . '">';
        
        if ($id) {
            echo '<input type="hidden" name="doc_id" value="' . $id . '">';
        }
        
        echo '
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="first_name" 
                               value="' . htmlspecialchars($d['doc_first_name'] ?? '') . '" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="last_name" 
                               value="' . htmlspecialchars($d['doc_last_name'] ?? '') . '" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" class="form-control" name="middle_init" maxlength="1"
                               value="' . htmlspecialchars($d['doc_middle_init'] ?? '') . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_num"
                               value="' . htmlspecialchars($d['doc_contact_num'] ?? '') . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Specialty *</label>
                        <select class="form-select" name="spec_id" required>';
        
        // Specializations dropdown
        if ($specializations['status'] === 'success') {
            foreach ($specializations['data'] as $spec) {
                $selected = ($spec['spec_id'] == ($d['spec_id'] ?? '')) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($spec['spec_id']) . '" ' . $selected . '>'
                     . htmlspecialchars($spec['spec_name'])
                     . '</option>';
            }
        } else {
            echo '<option value="">No specializations available</option>';
        }
        
        echo '          </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email"
                           value="' . htmlspecialchars($d['doc_email'] ?? '') . '">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    ' . ($id ? 'Update' : 'Add') . ' Doctor
                </button>
            </div>
        </form>';
    } else {
        echo '<div class="p-3 text-center text-danger">Unable to load doctor data.</div>';
    }
}

/* --------------------------
   ✏️ PATIENT FORM SECTION  
-------------------------- */
elseif ($type === 'patient_form') {
    $patient_data = $id ? $patient->getPatientById($id) : ['status' => 'success', 'data' => []];
    
    if ($patient_data['status'] === 'success') {
        $p = $patient_data['data'];
        
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/user_management.php">
            <input type="hidden" name="action" value="' . ($id ? 'update_patient' : 'add_patient') . '">';
        
        if ($id) {
            echo '<input type="hidden" name="pat_id" value="' . $id . '">';
        }
        
        echo '
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="pat_first_name" 
                               value="' . htmlspecialchars($p['pat_first_name'] ?? '') . '" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="pat_last_name" 
                               value="' . htmlspecialchars($p['pat_last_name'] ?? '') . '" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" class="form-control" name="pat_middle_init" maxlength="1"
                               value="' . htmlspecialchars($p['pat_middle_init'] ?? '') . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" class="form-control" name="pat_dob" 
                               value="' . htmlspecialchars($p['pat_dob'] ?? '') . '" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Gender *</label>
                        <select class="form-select" name="pat_gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" ' . (($p['pat_gender'] ?? '') === 'Male' ? 'selected' : '') . '>Male</option>
                            <option value="Female" ' . (($p['pat_gender'] ?? '') === 'Female' ? 'selected' : '') . '>Female</option>
                            <option value="Other" ' . (($p['pat_gender'] ?? '') === 'Other' ? 'selected' : '') . '>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="pat_contact_num"
                               value="' . htmlspecialchars($p['pat_contact_num'] ?? '') . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="pat_email"
                               value="' . htmlspecialchars($p['pat_email'] ?? '') . '">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="pat_address" rows="3">' . htmlspecialchars($p['pat_address'] ?? '') . '</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    ' . ($id ? 'Update' : 'Add') . ' Patient
                </button>
            </div>
        </form>';
    } else {
        echo '<div class="p-3 text-center text-danger">Unable to load patient data.</div>';
    }
}

/* --------------------------
   ✏️ STAFF FORM SECTION
-------------------------- */
elseif ($type === 'staff_form') {
    $staff_data = $id ? $staff->getStaffById($id) : ['status' => 'success', 'data' => []];
    
    if ($staff_data['status'] === 'success') {
        $s = $staff_data['data'];
        
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/user_management.php">
            <input type="hidden" name="action" value="' . ($id ? 'update_staff' : 'add_staff') . '">';
        
        if ($id) {
            echo '<input type="hidden" name="staff_id" value="' . $id . '">';
        }
        
        echo '
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="staff_first_name" 
                               value="' . htmlspecialchars($s['staff_first_name'] ?? '') . '" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="staff_last_name" 
                               value="' . htmlspecialchars($s['staff_last_name'] ?? '') . '" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" class="form-control" name="staff_middle_init" maxlength="1"
                               value="' . htmlspecialchars($s['staff_middle_init'] ?? '') . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="staff_contact_num"
                               value="' . htmlspecialchars($s['staff_contact_num'] ?? '') . '">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="staff_email"
                           value="' . htmlspecialchars($s['staff_email'] ?? '') . '">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    ' . ($id ? 'Update' : 'Add') . ' Staff
                </button>
            </div>
        </form>';
    } else {
        echo '<div class="p-3 text-center text-danger">Unable to load staff data.</div>';
    }
}

/* --------------------------
   ✏️ USER ACCOUNT FORM SECTION
-------------------------- */
elseif ($type === 'user_account_form') {
    // Get available doctors, patients, and staff for dropdowns
    $doctors_list = $doctor->getAllDoctors();
    $patients_list = $patient->getAllPatient();
    $staff_list = $staff->getAllStaff();
    
    echo '<form method="POST" action="/AP3%20Clinic%20System/pages/user_management.php">
        <input type="hidden" name="action" value="add_user">
        
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" class="form-control" name="user_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" class="form-control" name="user_password" required>
                    <small class="text-muted">Password must be at least 6 characters long</small>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Link to Profile (Required)</label>
                <div class="border rounded p-3 bg-light">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Patient</label>
                            <select class="form-select" name="pat_id">
                                <option value="">No Patient</option>';
    
    if ($patients_list['status'] === 'success') {
        foreach ($patients_list['data'] as $pat) {
            echo '<option value="' . $pat['pat_id'] . '">' . 
                 htmlspecialchars($pat['pat_first_name'] . ' ' . $pat['pat_last_name']) . 
                 ' (ID: ' . $pat['pat_id'] . ')</option>';
        }
    }
echo '              </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Doctor</label>
                            <select class="form-select" name="doc_id">
                                <option value="">No Doctor</option>';
    
    if ($doctors_list['status'] === 'success') {
        foreach ($doctors_list['data'] as $doc) {
            echo '<option value="' . $doc['doc_id'] . '">Dr. ' . 
                 htmlspecialchars($doc['doc_first_name'] . ' ' . $doc['doc_last_name']) . 
                 ' (ID: ' . $doc['doc_id'] . ')</option>';
        }
    }
    
    echo '              </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Staff</label>
                            <select class="form-select" name="staff_id">
                                <option value="">No Staff</option>';
    
    if ($staff_list['status'] === 'success') {
        foreach ($staff_list['data'] as $stf) {
            echo '<option value="' . $stf['staff_id'] . '">' . 
                 htmlspecialchars($stf['staff_first_name'] . ' ' . $stf['staff_last_name']) . 
                 ' (ID: ' . $stf['staff_id'] . ')</option>';
        }
    } echo '              </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- REMOVED SUPERADMIN CHECKBOX FOR SAFETY -->
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-teal">Create User Account</button>
        </div>
    </form>';
}

// Handle invalid requests
else {
    echo '<div class="p-3 text-center text-danger">Invalid request type: ' . htmlspecialchars($type) . '</div>';
}
?>