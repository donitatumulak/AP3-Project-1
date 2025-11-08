<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="payment_management.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_payment">
                    
                    <div class="mb-3">
                        <label for="appt_id" class="form-label">Appointment ID</label>
                        <input type="number" class="form-control" id="appt_id" name="appt_id" placeholder="Optional">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pymt_meth_id" class="form-label">Payment Method *</label>
                            <select class="form-control" id="pymt_meth_id" name="pymt_meth_id" required>
                                <option value="">Select Payment Method</option>
                                <?php if ($payment_methods['status'] === 'success'): ?>
                                    <?php foreach ($payment_methods['data'] as $method): ?>
                                        <option value="<?php echo $method['pymt_meth_id']; ?>">
                                            <?php echo htmlspecialchars($method['pymt_meth_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pymt_stat_id" class="form-label">Payment Status *</label>
                            <select class="form-control" id="pymt_stat_id" name="pymt_stat_id" required>
                                <option value="">Select Payment Status</option>
                                <?php if ($payment_statuses['status'] === 'success'): ?>
                                    <?php foreach ($payment_statuses['data'] as $status): ?>
                                        <option value="<?php echo $status['pymt_stat_id']; ?>">
                                            <?php echo htmlspecialchars($status['pymt_stat_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pymt_amount_paid" class="form-label">Amount Paid (₱) *</label>
                        <input type="number" class="form-control" id="pymt_amount_paid" name="pymt_amount_paid" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pymt_date" class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" id="pymt_date" name="pymt_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editPaymentModalBody">
                <!-- Form will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentMethodModalLabel">Add New Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="payment_management.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_payment_method">
                    
                    <div class="mb-3">
                        <label for="pymt_meth_name" class="form-label">Payment Method Name *</label>
                        <input type="text" class="form-control" id="pymt_meth_name" name="pymt_meth_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Payment Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Method Modal -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentMethodModalLabel">Edit Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editPaymentMethodModalBody">
                <!-- Form will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Status Modal -->
<div class="modal fade" id="addPaymentStatusModal" tabindex="-1" aria-labelledby="addPaymentStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentStatusModalLabel">Add New Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="payment_management.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_payment_status">
                    
                    <div class="mb-3">
                        <label for="pymt_stat_name" class="form-label">Payment Status Name *</label>
                        <input type="text" class="form-control" id="pymt_stat_name" name="pymt_stat_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Payment Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Status Modal -->
<div class="modal fade" id="editPaymentStatusModal" tabindex="-1" aria-labelledby="editPaymentStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentStatusModalLabel">Edit Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editPaymentStatusModalBody">
                <!-- Form will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>