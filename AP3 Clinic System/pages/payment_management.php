<?php
session_start();
$user_type = $_SESSION['user_type'] ?? '';
$is_staff = ($user_type === 'staff');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Allow access to: superadmin, admin, doctor, and staff (with restrictions)
$allowed_users = ['superadmin', 'admin', 'doctor', 'staff'];
if (!in_array($user_type, $allowed_users)) {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. You don't have permission to view this page.</div>";
    require_once '../includes/footer.php';
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->connect();

require_once '../classes/payments/Payment.php';
require_once '../classes/payments/PaymentMethod.php';
require_once '../classes/payments/PaymentStatus.php';
require_once '../classes/appointments/Appointment.php';

// Initialize classes
$payment = new Payment($db);
$paymentMethod = new PaymentMethod($db);
$paymentStatus = new PaymentStatus($db);
$appointment = new Appointment($db);

// Handle form submissions (with staff restrictions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        // Payment actions - staff can do everything except delete
        case 'add_payment':
            $result = $payment->addPayment(
                $_POST['pymt_meth_id'],
                $_POST['pymt_stat_id'],
                $_POST['pymt_amount_paid'],
                $_POST['pymt_date'],
                $_POST['appt_id'] ?? null
            );
            break;
            
        case 'update_payment':
            $result = $payment->updatePayment(
                $_POST['pymt_id'],
                $_POST['pymt_meth_id'],
                $_POST['pymt_stat_id'],
                $_POST['pymt_amount_paid'],
                $_POST['pymt_date'],
                $_POST['appt_id'] ?? null
            );
            break;
            
        case 'delete_payment':
            if (!$is_staff) {
                $result = $payment->deletePayment($_POST['pymt_id']);
            }
            break;
            
        // Payment Method actions - staff can do everything except delete
        case 'add_payment_method':
            $result = $paymentMethod->addPaymentMethod($_POST['pymt_meth_name']);
            break;
            
        case 'update_payment_method':
            $result = $paymentMethod->updatePaymentMethod(
                $_POST['pymt_meth_id'],
                $_POST['pymt_meth_name']
            );
            break;
            
        case 'delete_payment_method':
            if (!$is_staff) {
                $result = $paymentMethod->deletePaymentMethod($_POST['pymt_meth_id']);
            }
            break;
            
        // Payment Status actions - staff can do everything except delete
        case 'add_payment_status':
            $result = $paymentStatus->addPaymentStatus($_POST['pymt_stat_name']);
            break;
            
        case 'update_payment_status':
            $result = $paymentStatus->updatePaymentStatus(
                $_POST['pymt_stat_id'],
                $_POST['pymt_stat_name']
            );
            break;
            
        case 'delete_payment_status':
            if (!$is_staff) {
                $result = $paymentStatus->deletePaymentStatus($_POST['pymt_stat_id']);
            }
            break;

        // Staff CAN use these update-only functions
        case 'update_payment_status_only':
            $result = $payment->updatePaymentStatusOnly(
                $_POST['pymt_id'],
                $_POST['pymt_stat_id']
            );
            break;

        case 'update_payment_method_only':
            $result = $payment->updatePaymentMethodOnly(
                $_POST['pymt_id'],
                $_POST['pymt_meth_id']
            );
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

// Get all data for display
$payments = $payment->getAllPayments();
$payment_methods = $paymentMethod->getAllPaymentMethods();
$payment_statuses = $paymentStatus->getAllPaymentStatus();

$page = 'pages/payment_management';
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
                            <h1><i class="fas fa-credit-card me-3"></i>Payment Management</h1>
                            <p class="text-muted mb-0">
                                <?php echo $is_staff ? 'Manage payments, methods, and statuses (No deletion)' : 'Manage payments, payment methods, and payment statuses'; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card">
                                <div class="stats-content">
                                    <i class="fas fa-money-bill-wave stats-icon"></i>
                                    <div class="stats-text">
                                        <div class="stats-number">
                                            <?php 
                                            $total = 0;
                                            if ($payments['status'] === 'success') $total += count($payments['data']);
                                            if ($payment_methods['status'] === 'success') $total += count($payment_methods['data']);
                                            if ($payment_statuses['status'] === 'success') $total += count($payment_statuses['data']);
                                            echo $total;
                                            ?>
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

                <!-- Payment Management Tabs -->
                <div class="management-tabs">
                    <nav>
                        <div class="nav nav-tabs" id="paymentTabs" role="tablist">
                            <button class="nav-link active" id="payments-tab" data-bs-toggle="tab" 
                                    data-bs-target="#payments" type="button" role="tab">
                                <i class="fas fa-receipt"></i>
                                Payments
                                <span class="badge bg-primary ms-1">
                                    <?php echo $payments['status'] === 'success' ? count($payments['data']) : 0; ?>
                                </span>
                            </button>

                            <button class="nav-link" id="methods-tab" data-bs-toggle="tab" 
                                    data-bs-target="#methods" type="button" role="tab">
                                <i class="fas fa-credit-card"></i>
                                Payment Methods
                                <span class="badge bg-success ms-1">
                                    <?php echo $payment_methods['status'] === 'success' ? count($payment_methods['data']) : 0; ?>
                                </span>
                            </button>

                            <button class="nav-link" id="statuses-tab" data-bs-toggle="tab" 
                                    data-bs-target="#statuses" type="button" role="tab">
                                <i class="fas fa-tags"></i>
                                Payment Statuses
                                <span class="badge bg-info ms-1">
                                    <?php echo $payment_statuses['status'] === 'success' ? count($payment_statuses['data']) : 0; ?>
                                </span>
                            </button>
                        </div>
                    </nav>

                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white">
                        <!-- Payments Tab -->
                        <div class="tab-pane fade show active" id="payments" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search payments..." 
                                                   id="search-payments">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddPaymentModal()">
                                            <i class="fas fa-plus"></i> Add Payment
                                        </button>
                                    </div>
                                </div>

                                <!-- Payments Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="payments-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th width="8%">Payment ID</th>
                                                <th width="20%">Patient</th>
                                                <th width="15%">Appointment</th>
                                                <th width="15%">Payment Method</th>
                                                <th width="8%">Status</th>
                                                <th width="10%">Amount</th>
                                                <th width="12%">Date</th>
                                                <th width="12%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($payments['status'] === 'success' && !empty($payments['data'])): ?>
                                                <?php foreach ($payments['data'] as $pymt): ?>
                                                <tr>
                                                    <td class="align-middle">
                                                        <span><?php echo $pymt['pymt_id']; ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <strong><?php echo htmlspecialchars($pymt['patient_name'] ?? 'N/A'); ?></strong>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?php if (!empty($pymt['appt_id'])): 
                                                            $apptDate = $pymt['appt_date'] ?? date('Y-m-d');
                                                            $formattedApptId = $appointment->formatAppointmentId($pymt['appt_id'], $apptDate);
                                                        ?>
                                                            <span class="badge pastel-green"><?php echo $formattedApptId; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge pastel-blue"><?php echo htmlspecialchars($pymt['payment_method']); ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?php 
                                                        $status_class = 'pastel-gray';
                                                        if ($pymt['payment_status'] === 'Paid') $status_class = 'pastel-green';
                                                        elseif ($pymt['payment_status'] === 'Pending') $status_class = 'pastel-orange';
                                                        elseif ($pymt['payment_status'] === 'Refunded') $status_class = 'pastel-pink';
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo htmlspecialchars($pymt['payment_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <strong class="text-success">₱<?php echo number_format($pymt['pymt_amount_paid'], 2); ?></strong>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?php echo date('M j, Y', strtotime($pymt['pymt_date'])); ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1 flex-wrap">
                                                            <!-- EDIT button - for correcting mistakes -->
                                                            <button class="btn btn-outline-warning btn-sm btn-action" 
                                                                    onclick="openEditPaymentModal(<?php echo $pymt['pymt_id']; ?>)"
                                                                    title="Edit Payment Details">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            
                                                            <!-- UPDATE STATUS button - for workflow progression -->
                                                            <button class="btn btn-outline-info btn-sm btn-action" 
                                                                    onclick="openUpdatePaymentStatusModal(<?php echo $pymt['pymt_id']; ?>)"
                                                                    title="Update Payment Status">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                            
                                                            <!-- UPDATE METHOD button - for changing payment method -->
                                                            <button class="btn btn-outline-secondary btn-sm btn-action" 
                                                                    onclick="openUpdatePaymentMethodModal(<?php echo $pymt['pymt_id']; ?>)"
                                                                    title="Update Payment Method">
                                                                <i class="fas fa-credit-card"></i>
                                                            </button>
                                                            
                                                            <?php if (!$is_staff): ?>
                                                                <button class="btn btn-outline-danger btn-sm btn-action"
                                                                        onclick="confirmDelete(<?php echo $pymt['pymt_id']; ?>, 'Payment #<?php echo $pymt['pymt_id']; ?>', 'payment', 'payment_management.php')"
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
                                                        <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                                        <?php echo $payments['status'] === 'error' ? 'Error: ' . htmlspecialchars($payments['message']) : 'No payments found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($payments['status'] === 'success' && !empty($payments['data'])): ?>
                                <nav aria-label="Payments pagination">
                                    <ul class="pagination justify-content-center mt-3" id="payments-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Payment Methods Tab -->
                        <div class="tab-pane fade" id="methods" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search payment methods..." 
                                                   id="search-methods">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddPaymentMethodModal()">
                                            <i class="fas fa-plus"></i> Add Payment Method
                                        </button>
                                    </div>
                                </div>

                                <!-- Payment Methods Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="methods-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th width="20%" class="text-center">Method ID</th>
                                                <th width="60%" class="text-center">Payment Method Name</th>
                                                <th width="20%" class="text-start ps-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($payment_methods['status'] === 'success' && !empty($payment_methods['data'])): ?>
                                                <?php foreach ($payment_methods['data'] as $method): ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <span><?php echo $method['pymt_meth_id']; ?></span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <h6 class="mb-0 text-teal"><?php echo htmlspecialchars($method['pymt_meth_name']); ?></h6>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-warning btn-sm btn-action" 
                                                                    onclick="openEditPaymentMethodModal(<?php echo $method['pymt_meth_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if (!$is_staff): ?>
                                                                <button class="btn btn-outline-danger btn-sm btn-action"
                                                                        onclick="confirmDelete(<?php echo $method['pymt_meth_id']; ?>, '<?php echo htmlspecialchars($method['pymt_meth_name']); ?>', 'payment_method', 'payment_management.php')"
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
                                                    <td colspan="3" class="text-center text-muted py-4">
                                                        <i class="fas fa-credit-card fa-2x mb-2 d-block"></i>
                                                        <?php echo $payment_methods['status'] === 'error' ? 'Error: ' . htmlspecialchars($payment_methods['message']) : 'No payment methods found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($payment_methods['status'] === 'success' && !empty($payment_methods['data'])): ?>
                                <nav aria-label="Payment methods pagination">
                                    <ul class="pagination justify-content-center mt-3" id="methods-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Payment Statuses Tab -->
                        <div class="tab-pane fade" id="statuses" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search payment statuses..." 
                                                   id="search-statuses">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddPaymentStatusModal()">
                                            <i class="fas fa-plus"></i> Add Payment Status
                                        </button>
                                    </div>
                                </div>

                                <!-- Payment Statuses Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="statuses-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th width="20%" class="text-center">Status ID</th>
                                                <th width="60%" class="text-center">Payment Status Name</th>
                                                <th width="20%" class="text-start ps-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($payment_statuses['status'] === 'success' && !empty($payment_statuses['data'])): ?>
                                                <?php foreach ($payment_statuses['data'] as $status): ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <span><?php echo $status['pymt_stat_id']; ?></span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <h6 class="mb-0 text-teal"><?php echo htmlspecialchars($status['pymt_stat_name']); ?></h6>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-warning btn-sm btn-action" 
                                                                    onclick="openEditPaymentStatusModal(<?php echo $status['pymt_stat_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if (!$is_staff): ?>
                                                                <button class="btn btn-outline-danger btn-sm btn-action"
                                                                        onclick="confirmDelete(<?php echo $status['pymt_stat_id']; ?>, '<?php echo htmlspecialchars($status['pymt_stat_name']); ?>', 'payment_status', 'payment_management.php')"
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
                                                    <td colspan="3" class="text-center text-muted py-4">
                                                        <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                                        <?php echo $payment_statuses['status'] === 'error' ? 'Error: ' . htmlspecialchars($payment_statuses['message']) : 'No payment statuses found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($payment_statuses['status'] === 'success' && !empty($payment_statuses['data'])): ?>
                                <nav aria-label="Payment statuses pagination">
                                    <ul class="pagination justify-content-center mt-3" id="statuses-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Reusable Modals -->
    <?php include '../components/management_modals.php'; ?>

    <!-- Payment Specific Modals -->
    <?php
    // Add Payment Modal (staff CAN use this)
    renderAddModal('addPaymentModal', 'Payment', 'payment_management.php', 'payment', [
        ['name' => 'appt_id', 'label' => 'Appointment ID', 'type' => 'number', 'required' => false, 'width' => 'col-md-6', 'placeholder' => 'Optional'],
        [
            'name' => 'pymt_meth_id',
            'label' => 'Payment Method',
            'type' => 'select',
            'required' => true,
            'width' => 'col-md-6',
            'options' => $payment_methods['status'] === 'success' ? $payment_methods['data'] : []
        ],
        [
            'name' => 'pymt_stat_id',
            'label' => 'Payment Status',
            'type' => 'select',
            'required' => true,
            'width' => 'col-md-6',
            'options' => $payment_statuses['status'] === 'success' ? $payment_statuses['data'] : []
        ],
        ['name' => 'pymt_amount_paid', 'label' => 'Amount Paid', 'type' => 'number', 'required' => true, 'width' => 'col-md-6', 'step' => '0.01', 'placeholder' => '0.00'],
        ['name' => 'pymt_date', 'label' => 'Payment Date', 'type' => 'date', 'required' => true, 'width' => 'col-md-6']
    ]);

    // Edit Payment Modal (staff CAN use this)
    renderEditModal('editPaymentModal', 'Payment', 'payment');

    // Add Payment Method Modal (staff CAN use this)
    renderAddModal('addPaymentMethodModal', 'Payment Method', 'payment_management.php', 'payment_method', [
        ['name' => 'pymt_meth_name', 'label' => 'Payment Method Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter payment method name...']
    ]);

    // Edit Payment Method Modal (staff CAN use this)
    renderEditModal('editPaymentMethodModal', 'Payment Method', 'payment_method');

    // Add Payment Status Modal (staff CAN use this)
    renderAddModal('addPaymentStatusModal', 'Payment Status', 'payment_management.php', 'payment_status', [
        ['name' => 'pymt_stat_name', 'label' => 'Payment Status Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter payment status name...']
    ]);

    // Edit Payment Status Modal (staff CAN use this)
    renderEditModal('editPaymentStatusModal', 'Payment Status', 'payment_status');
    ?>

     <div class="modal fade" id="updatePaymentStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-sync-alt me-2"></i>Update Payment Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updatePaymentStatusModalBody">
                    <!-- Form will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update Payment Method Modal -->
    <div class="modal fade" id="updatePaymentMethodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-credit-card me-2"></i>Update Payment Method
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updatePaymentMethodModalBody">
                    <!-- Form will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert + JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/management.js"></script>

    <script>
    // Payment Functions
    function openAddPaymentModal() {
        const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        modal.show();
    }

    function openEditPaymentModal(paymentId) {
        const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
        fetch(`../handlers/payments/payments_form_handler.php?action=get_payment_form&id=${paymentId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editPaymentModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load payment form', 'error');
            });
    }

    // Update Payment Status Modal
    function openUpdatePaymentStatusModal(paymentId) {
        const modal = new bootstrap.Modal(document.getElementById('updatePaymentStatusModal'));
        fetch(`../handlers/payments/payments_form_handler.php?action=get_update_payment_status_form&id=${paymentId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('updatePaymentStatusModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load update status form', 'error');
            });
    }

    // Update Payment Method Modal
    function openUpdatePaymentMethodModal(paymentId) {
        const modal = new bootstrap.Modal(document.getElementById('updatePaymentMethodModal'));
        fetch(`../handlers/payments/payments_form_handler.php?action=get_update_payment_method_form&id=${paymentId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('updatePaymentMethodModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load update method form', 'error');
            });
    }

    // Payment Method Functions
    function openAddPaymentMethodModal() {
        const modal = new bootstrap.Modal(document.getElementById('addPaymentMethodModal'));
        modal.show();
    }

    function openEditPaymentMethodModal(methodId) {
        const modal = new bootstrap.Modal(document.getElementById('editPaymentMethodModal'));
        fetch(`../handlers/payments/payments_form_handler.php?action=get_payment_method_form&id=${methodId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editPaymentMethodModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load payment method form', 'error');
            });
    }

    // Payment Status Functions
    function openAddPaymentStatusModal() {
        const modal = new bootstrap.Modal(document.getElementById('addPaymentStatusModal'));
        modal.show();
    }

    function openEditPaymentStatusModal(statusId) {
        const modal = new bootstrap.Modal(document.getElementById('editPaymentStatusModal'));
        fetch(`../handlers/payments/payments_form_handler.php?action=get_payment_status_form&id=${statusId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editPaymentStatusModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load payment status form', 'error');
            });
    }

    // Initialize management features
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize search functionality for all tables
        initializeSearch('search-payments', 'payments-table');
        initializeSearch('search-methods', 'methods-table');
        initializeSearch('search-statuses', 'statuses-table');
        
        // Initialize pagination for all tables
        initializePagination('payments-table', 'payments-pagination', 10);
        initializePagination('methods-table', 'methods-pagination', 10);
        initializePagination('statuses-table', 'statuses-pagination', 10);
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Activate tab based on URL hash on page load
        const hash = window.location.hash;
        if (hash) {
            const tabButton = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
        
        // 2. UPDATE URL WHEN TABS ARE CLICKED MANUALLY (THIS IS WHAT'S MISSING)
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                // Update URL hash without page reload
                if (history.pushState) {
                    history.pushState(null, null, target);
                } else {
                    window.location.hash = target;
                }
            });
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>