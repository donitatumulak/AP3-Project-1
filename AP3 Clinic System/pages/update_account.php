<?php
// Start session and handle ALL logic BEFORE any HTML output
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_type'];

require_once '../config/Database.php';
require_once '../classes/users/User.php';
require_once '../classes/users/Doctor.php';
require_once '../classes/users/Staff.php';
require_once '../classes/users/Patient.php';

$database = new Database();
$db = $database->connect();

$userObj = new User($db);
$userRes = $userObj->getUserById($user_id);

if ($userRes['status'] !== 'success' || empty($userRes['data'])) {
    // Set error in session and redirect
    $_SESSION['error'] = "Unable to retrieve user data.";
    header("Location: my_account.php");
    exit();
}

$userData = $userRes['data'];
$message = "";

// Fetch role-specific data
switch ($user_role) {
    case 'doctor':
        $class = new Doctor($db);
        $res = $class->getDoctorById($userData['doc_id']);
        $data = $res['data'] ?? [];
        break;
    case 'staff':
        $class = new Staff($db);
        $res = $class->getStaffById($userData['staff_id']);
        $data = $res['data'] ?? [];
        break;
    case 'patient':
        $class = new Patient($db);
        $res = $class->getPatientById($userData['pat_id']);
        $data = $res['data'] ?? [];
        break;
    default:
        $_SESSION['error'] = "Invalid user role.";
        header("Location: my_account.php");
        exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = false;
    
    switch ($user_role) {
        case 'doctor':
            $result = $class->updateDoctor(
                $data['doc_id'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['middle_init'],
                $_POST['contact_num'],
                $_POST['email'],
                $data['spec_id']
            );
            $success = ($result['status'] === 'success');
            break;
        case 'staff':
            $result = $class->updateStaff(
                $data['staff_id'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['middle_init'],
                $_POST['contact_num'],
                $_POST['email']
            );
            $success = ($result['status'] === 'success');
            break;
        case 'patient':
            $result = $class->updatePatient(
                $data['pat_id'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['middle_init'],
                $_POST['dob'],
                $_POST['gender'],
                $_POST['contact_num'],
                $_POST['email'],
                $_POST['address']
            );
            $success = ($result['status'] === 'success');
            break;
    }

    if ($success) {
        $_SESSION['success'] = "Account updated successfully!";
        header("Location: my_account.php");
        exit();
    } else {
        $message = $result['message'] ?? 'Update failed. Please try again.';
    }
}

// Only after all PHP logic, include the header files and output HTML
$page = 'pages/update_account';
require_once '../includes/header.php';
require_once '../includes/sidebar_user.php';
?>

<body class="account-page">
<main class="main-content p-4 flex-grow-1">
  <div class="container py-4">
    <div class="row justify-content-center">

      <?php if ($user_role === 'patient'): ?>
        <div class="col-lg-10 col-md-11">
      <?php else: ?>
        <div class="col-lg-7 col-md-9">
      <?php endif; ?>

          <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
              <h3 class="text-center mb-4 fw-bold text-teal">Update My Details</h3>

              <?php if ($message): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($message) ?></div>
              <?php endif; ?>

              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
              <?php endif; ?>

              <form method="POST" autocomplete="off" class="row g-3">

                <?php if ($user_role === 'patient'): ?>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($data['pat_first_name']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($data['pat_last_name']) ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Middle Initial</label>
                    <input type="text" name="middle_init" class="form-control" value="<?= htmlspecialchars($data['pat_middle_init']) ?>">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Gender</label>
                    <select name="gender" class="form-select">
                      <option value="Male" <?= ($data['pat_gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                      <option value="Female" <?= ($data['pat_gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                      <option value="Other" <?= ($data['pat_gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                      <option value="Prefer not to say" <?= ($data['pat_gender'] === 'Prefer not to say') ? 'selected' : '' ?>>Prefer not to say</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($data['pat_dob']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact_num" class="form-control" value="<?= htmlspecialchars($data['pat_contact_num']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['pat_email']) ?>">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($data['pat_address']) ?></textarea>
                  </div>

                <?php elseif ($user_role === 'doctor'): ?>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($data['doc_first_name']) ?>" required>
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($data['doc_last_name']) ?>" required>
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Middle Initial</label>
                    <input type="text" name="middle_init" class="form-control" value="<?= htmlspecialchars($data['doc_middle_init']) ?>">
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact_num" class="form-control" value="<?= htmlspecialchars($data['doc_contact_num']) ?>">
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['doc_email']) ?>">
                  </div>

                <?php elseif ($user_role === 'staff'): ?>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($data['staff_first_name']) ?>" required>
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($data['staff_last_name']) ?>" required>
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Middle Initial</label>
                    <input type="text" name="middle_init" class="form-control" value="<?= htmlspecialchars($data['staff_middle_init']) ?>">
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact_num" class="form-control" value="<?= htmlspecialchars($data['staff_contact_num']) ?>">
                  </div>
                  <div class="col-md-10 mx-auto">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['staff_email']) ?>">
                  </div>
                <?php endif; ?>

               <div class="col-12 text-center mt-4 d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2">
                  <button type="submit" class="btn btn-teal px-4 me-2">
                    <i class="bi bi-save me-1"></i> Save Changes
                  </button>
                  <a href="my_account.php" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left me-1"></i> Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div> <!-- col -->
    </div> <!-- row -->
  </div> <!-- container -->
</main>

<?php require_once '../includes/footer.php'; ?>