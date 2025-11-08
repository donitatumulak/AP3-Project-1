<?php
$page = 'pages/change_pass';
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
require_once '../includes/sidebar_user.php';
?>

<body class="account-page">
<div class="main-content p-4">
    <div class="container">
        <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 750px;">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold text-teal m-0">Change Password</h3>
                    <a href="my_account.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Account
                    </a>
                </div>

                <!-- PASSWORD FORM SECTION -->
                <h5 class="text-muted mb-3 border-bottom pb-2">Update Your Password</h5>
                
                <form id="changePasswordForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Current Password *</label>
                                <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">New Password *</label>
                                <input type="password" name="new_password" id="newPassword" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password *</label>
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                                <div class="invalid-feedback" id="passwordMismatchMsg">Passwords do not match.</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-teal px-4">
                            <i class="fas fa-key me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('changePasswordForm');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const mismatchMsg = document.getElementById('passwordMismatchMsg');

    function validatePasswords() {
        if (confirmPassword.value && newPassword.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
            return false;
        } else {
            confirmPassword.classList.remove('is-invalid');
            return true;
        }
    }

    newPassword.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        if (!validatePasswords()) return;

        const formData = new FormData(form);

        fetch('../handlers/change_password_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Updated!',
                    text: 'Please log in again with your new password.',
                    confirmButtonColor: '#00897B'
                }).then(() => {
                    window.location.href = '../login.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message,
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(() => {
            Swal.fire('Error!', 'Something went wrong while updating your password.', 'error');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>