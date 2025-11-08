<?php
session_start();
require_once  '../../config/Database.php';
require_once  '../../classes/payments/Payment.php';
require_once  '../../classes/payments/PaymentMethod.php';
require_once  '../../classes/payments/PaymentStatus.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    if (!is_numeric($id)) {
        echo '<div class="p-3 text-danger text-center">Invalid ID.</div>';
        exit;
    }

    try {
        $database = new Database();
        $db = $database->connect();
        
        switch ($action) {
            case 'get_payment_form':
            $payment = new Payment($db);
            $paymentMethod = new PaymentMethod($db);
            $paymentStatus = new PaymentStatus($db);
            
            $result = $payment->getPaymentById($id);
            $methods = $paymentMethod->getAllPaymentMethods();
            $statuses = $paymentStatus->getAllPaymentStatus();
            
            if ($result['status'] === 'success' && !empty($result['data'])) {
                $payment_data = $result['data'];
                ?>
                <form method="POST" action="payment_management.php">
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="pymt_id" value="<?= $payment_data['pymt_id'] ?>">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Appointment ID</label>
                                <input type="number" class="form-control" name="appt_id" 
                                    value="<?= $payment_data['appt_id'] ?? '' ?>" placeholder="Optional">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" name="pymt_date" 
                                    value="<?= $payment_data['pymt_date'] ?>" required>
                            </div>
                        </div>

                        <!-- ✅ FIXED DROPDOWN: USE IDs -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select class="form-select" name="pymt_meth_id" required>
                                    <option value="">Select Payment Method</option>
                                    <?php if ($methods['status'] === 'success' && !empty($methods['data'])): ?>
                                        <?php foreach ($methods['data'] as $method): ?>
                                            <option value="<?= $method['pymt_meth_id'] ?>" 
                                                <?= ($method['pymt_meth_id'] == $payment_data['pymt_meth_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($method['pymt_meth_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Status *</label>
                                <select class="form-select" name="pymt_stat_id" required>
                                    <option value="">Select Payment Status</option>
                                    <?php if ($statuses['status'] === 'success' && !empty($statuses['data'])): ?>
                                        <?php foreach ($statuses['data'] as $status): ?>
                                            <option value="<?= $status['pymt_stat_id'] ?>" 
                                                <?= ($status['pymt_stat_id'] == $payment_data['pymt_stat_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status['pymt_stat_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount Paid *</label>
                            <input type="number" class="form-control" name="pymt_amount_paid" 
                                value="<?= $payment_data['pymt_amount_paid'] ?>" step="0.01" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal">Save Changes</button>
                    </div>
                </form>
                <?php
            } else {
                echo '<div class="p-3 text-center text-danger">Payment not found.</div>';
            }
            break;

            case 'get_update_payment_status_form':
                if (!isset($_GET['id'])) {
                    echo "Error: Payment ID is required";
                    exit;
                }
                
                $payment_id = $_GET['id'];
                
                // Get current payment data
                $stmt = $db->prepare("
                    SELECT p.*, ps.pymt_stat_name 
                    FROM payment p 
                    LEFT JOIN payment_status ps ON p.pymt_stat_id = ps.pymt_stat_id 
                    WHERE p.pymt_id = ?
                ");
                $stmt->execute([$payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$payment) {
                    echo "Error: Payment not found";
                    exit;
                }
                
                // Get all status options
                $status_stmt = $db->prepare("SELECT * FROM payment_status ORDER BY pymt_stat_name");
                $status_stmt->execute();
                $statuses = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                ?>
                <form action="../pages/payment_management.php" method="POST">
                    <input type="hidden" name="action" value="update_payment_status_only">
                    <input type="hidden" name="pymt_id" value="<?php echo $payment_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payment['pymt_stat_name']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pymt_stat_id" class="form-label">New Payment Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="pymt_stat_id" name="pymt_stat_id" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['pymt_stat_id']; ?>" 
                                    <?php echo $status['pymt_stat_id'] == $payment['pymt_stat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status['pymt_stat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-sync-alt me-1"></i> Update Status
                        </button>
                    </div>
                </form>
                <?php
                break;

            case 'get_update_payment_method_form':
                if (!isset($_GET['id'])) {
                    echo "Error: Payment ID is required";
                    exit;
                }
                
                $payment_id = $_GET['id'];
                
                // Get current payment data
                $stmt = $db->prepare("
                    SELECT p.*, pm.pymt_meth_name 
                    FROM payment p 
                    LEFT JOIN payment_method pm ON p.pymt_meth_id = pm.pymt_meth_id 
                    WHERE p.pymt_id = ?
                ");
                $stmt->execute([$payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$payment) {
                    echo "Error: Payment not found";
                    exit;
                }
                
                // Get all method options
                $method_stmt = $db->prepare("SELECT * FROM payment_method ORDER BY pymt_meth_name");
                $method_stmt->execute();
                $methods = $method_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                ?>
                <form action="../pages/payment_management.php" method="POST">
                    <input type="hidden" name="action" value="update_payment_method_only">
                    <input type="hidden" name="pymt_id" value="<?php echo $payment_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Current Method</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payment['pymt_meth_name']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pymt_meth_id" class="form-label">New Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="pymt_meth_id" name="pymt_meth_id" required>
                            <option value="">Select Method</option>
                            <?php foreach ($methods as $method): ?>
                                <option value="<?php echo $method['pymt_meth_id']; ?>" 
                                    <?php echo $method['pymt_meth_id'] == $payment['pymt_meth_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($method['pymt_meth_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-credit-card me-1"></i> Update Method
                        </button>
                    </div>
                </form>
                <?php
            break;
            
            case 'get_payment_method_form':
                $paymentMethod = new PaymentMethod($db);
                $result = $paymentMethod->getPaymentMethodById($id);
                
                if ($result['status'] === 'success' && !empty($result['data'])) {
                    $method_data = $result['data'];
                    ?>
                    <form method="POST" action="payment_management.php">
                        <input type="hidden" name="action" value="update_payment_method">
                        <input type="hidden" name="pymt_meth_id" value="<?= $method_data['pymt_meth_id'] ?>">
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Payment Method Name *</label>
                                <input type="text" class="form-control" name="pymt_meth_name" 
                                    value="<?= htmlspecialchars($method_data['pymt_meth_name']) ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-teal">Save Changes</button>
                        </div>
                    </form>
                    <?php
                } else {
                    echo '<div class="p-3 text-center text-danger">Payment method not found.</div>';
                }
                break;
                
            case 'get_payment_status_form':
                $paymentStatus = new PaymentStatus($db);
                $result = $paymentStatus->getPaymentStatusById($id);
                
                if ($result['status'] === 'success' && !empty($result['data'])) {
                    $status_data = $result['data'];
                    ?>
                    <form method="POST" action="payment_management.php">
                        <input type="hidden" name="action" value="update_payment_status">
                        <input type="hidden" name="pymt_stat_id" value="<?= $status_data['pymt_stat_id'] ?>">
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Payment Status Name *</label>
                                <input type="text" class="form-control" name="pymt_stat_name" 
                                    value="<?= htmlspecialchars($status_data['pymt_stat_name']) ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-teal">Save Changes</button>
                        </div>
                    </form>
                    <?php
                } else {
                    echo '<div class="p-3 text-center text-danger">Payment status not found.</div>';
                }
                break;
                
            default:
                echo '<div class="p-3 text-center text-danger">Invalid action.</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="p-3 text-center text-danger">Server error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="p-3 text-center text-danger">Invalid request.</div>';
}
?>