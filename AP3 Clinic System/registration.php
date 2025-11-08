<?php 
session_start();
$page = 'auth'; 
include 'includes/header.php'; 
?>

<body class="auth-page bg-light">
<?php include 'includes/navbar_simple.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['success_message'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        text: '<?php echo addslashes($_SESSION['success_message']); ?>',
        confirmButtonColor: '#20c997',
        confirmButtonText: 'Continue to Login',
        timer: 4000,
        timerProgressBar: true
    });
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: '<?php echo addslashes($_SESSION['error_message']); ?>',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Try Again'
    });
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
});
</script>

<main class="d-flex justify-content-center align-items-center py-4" style="margin-top: 66.8px;">
  <div class="container">
    <div class="row justify-content-center align-items-stretch shadow-lg rounded-4 overflow-hidden bg-white" style="max-width: 950px; margin: auto;">
      
      <!-- Left side: Image -->
      <div class="col-md-6 d-none d-md-block p-0">
        <div class="auth-image" 
             style="background: url('assets/images/cura_clinic5.jpg') center center / cover no-repeat; height: 100%; min-height: 500px;">
        </div>
      </div>

      <!-- Right side: Registration Card -->
      <div class="col-md-6 d-flex align-items-center justify-content-center p-5 bg-white">
        <div class="w-100" style="max-width: 420px;">
          
          <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill fs-1 text-teal"></i>
            <h2 class="fw-bold mt-2">Create Your Account</h2>
            <p class="text-muted">Fill out your information to register as a patient.</p>
          </div>

          <form action="handlers/auth/register_handler.php" method="POST">

            <!-- SECTION 1: Personal Information -->
            <h5 class="text-teal mb-3">Personal Information</h5>

            <div class="mb-3">
              <label for="lname" class="form-label">Last Name</label>
              <input type="text" class="form-control form-control-lg rounded-3" id="lname" name="lname" required>
            </div>

            <div class="mb-3">
              <label for="fname" class="form-label">First Name</label>
              <input type="text" class="form-control form-control-lg rounded-3" id="fname" name="fname" required>
            </div>

            <div class="mb-3">
              <label for="mname" class="form-label">Middle Initial</label>
              <input type="text" class="form-control form-control-lg rounded-3" id="mname" name="mname" maxlength="1" placeholder="e.g. A">
            </div>

            <div class="mb-3">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" class="form-control form-control-lg rounded-3" id="dob" name="dob" required>
            </div>

            <div class="mb-3">
              <label for="gender" class="form-label">Gender</label>
              <select class="form-select form-select-lg rounded-3" id="gender" name="gender" required>
                <option value="" selected disabled>Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
                <option value="Prefer not to say">Prefer not to say</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="contact" class="form-label">Contact Number</label>
              <input type="text" class="form-control form-control-lg rounded-3" id="contact" name="contact" placeholder="e.g. 09XXXXXXXXX" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control form-control-lg rounded-3" id="email" name="email" placeholder="e.g. name@email.com" required>
            </div>

            <div class="mb-4">
              <label for="address" class="form-label">Home Address</label>
              <input type="text" class="form-control form-control-lg rounded-3" id="address" name="address" required>
            </div>

            <hr class="my-4">

            <!-- SECTION 2: Account Information -->
            <h5 class="text-teal mb-3">Account Information</h5>

            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control form-control-lg rounded-3" id="username" name="username" required>
            </div>

            <div class="mb-4">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control form-control-lg rounded-3" id="password" name="password" required>
            </div>

             <div class="mb-4">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input type="password" class="form-control form-control-lg rounded-3" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-teal w-100 py-2 rounded-3 fw-semibold">Register</button>

            <div class="text-center mt-4 small">
              Already have an account? 
              <a href="login.php" class="text-teal fw-semibold text-decoration-none">Login here</a>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>
</main>

<script>
// Client-side form validation with live confirm password check
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    // Create a small helper text element below the confirm field
    const confirmFeedback = document.createElement('div');
    confirmFeedback.classList.add('invalid-feedback');
    confirmInput.insertAdjacentElement('afterend', confirmFeedback);

    // Function to check if passwords match (live)
    function checkPasswordsMatch() {
        const password = passwordInput.value.trim();
        const confirm = confirmInput.value.trim();

        if (confirm === '') {
            confirmInput.classList.remove('is-invalid', 'is-valid');
            confirmFeedback.textContent = '';
            return;
        }

        if (password !== confirm) {
            confirmInput.classList.add('is-invalid');
            confirmInput.classList.remove('is-valid');
            confirmFeedback.textContent = 'Passwords do not match.';
        } else {
            confirmInput.classList.remove('is-invalid');
            confirmInput.classList.add('is-valid');
            confirmFeedback.textContent = '';
        }
    }

    // Live event listeners
    passwordInput.addEventListener('input', checkPasswordsMatch);
    confirmInput.addEventListener('input', checkPasswordsMatch);

    // Final validation on submit
    form.addEventListener('submit', function(e) {
        let valid = true;
        const password = passwordInput.value.trim();

        // Check password length
        if (password.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password must be at least 6 characters long.'
            });
            valid = false;
        }

        // Check if passwords match
        if (passwordInput.value !== confirmInput.value) {
            confirmInput.classList.add('is-invalid');
            confirmFeedback.textContent = 'Passwords do not match.';
            valid = false;
        }

        // Check if username is alphanumeric
        const username = document.getElementById('username').value.trim();
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Username',
                text: 'Username can only contain letters, numbers, and underscores.'
            });
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
});
</script>


<?php include 'includes/footer.php'; ?>
