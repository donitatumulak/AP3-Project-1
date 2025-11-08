<?php 
session_start();
$page = 'auth'; 
include 'includes/header.php'; 
?>

<body class="auth-page bg-light">
<?php include 'includes/navbar_simple.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['success_message'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        text: 'You can now login with your credentials.',
        confirmButtonColor: '#20c997',
        confirmButtonText: 'Continue to Login',
        timer: 5000,
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
                 <div class="auth-image" style="background: url('assets/images/cura_clinic6.jpg') center center / cover no-repeat; height: 100%; min-height: 500px;"></div>
            </div>

            <!-- Right side: Login Card -->
            <div class="col-md-6 d-flex align-items-center justify-content-center p-5 bg-white">
                <div class="w-100" style="max-width: 400px;">
                    <div class="text-center mb-4">
                        <i class="bi bi-heart-pulse-fill fs-1 text-teal"></i>
                        <h2 class="fw-bold mt-2">Log in to Cura Clinic</h2>
                        <p class="text-muted">Welcome back! Please enter your details.</p>
                    </div>

                    <!-- Error Message Display -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Success Message Display (for registration, etc.) -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['success']; 
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Update form action to point to login_process.php -->
                    <form action="handlers/auth/login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="username" name="username" 
                                   placeholder="Enter username" 
                                   value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg rounded-3" id="password" name="password" 
                                   placeholder="Enter password" required>
                        </div>

                        <button type="submit" class="btn btn-teal w-100 py-2 rounded-3 fw-semibold">Login</button>

                        <div class="text-center mt-4 small">
                            Don't have an account?
                            <a href="registration.php" class="text-teal fw-semibold text-decoration-none">Register here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>