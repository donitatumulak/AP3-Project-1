<?php
class Payment {
    private $conn;
    private $user_type;
    private $user_id;
    private $table = "payment";

    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation
    private function validatePaymentData($pymt_meth_id, $pymt_stat_id, $pymt_amount_paid, $pymt_date, $appt_id = null, $pymt_id = null) {
        $errors = [];

        if (empty($pymt_meth_id) || !is_numeric($pymt_meth_id)) {
            $errors[] = "Valid payment method ID is required.";
        }
        if (empty($pymt_stat_id) || !is_numeric($pymt_stat_id)) {
            $errors[] = "Valid payment status ID is required.";
        }
        if (empty($pymt_amount_paid) || !is_numeric($pymt_amount_paid) || $pymt_amount_paid <= 0) {
            $errors[] = "Valid payment amount is required.";
        }
        if (empty(trim($pymt_date))) {
            $errors[] = "Payment date is required.";
        }
        if ($appt_id !== null && (!is_numeric($appt_id) || $appt_id <= 0)) {
            $errors[] = "Valid appointment ID is required.";
        }
        if ($pymt_id !== null && (!is_numeric($pymt_id) || $pymt_id <= 0)) {
            $errors[] = "Valid payment ID is required.";
        }

        return $errors;
    }

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
        $this->user_type = $_SESSION['user_type'] ?? '';
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

    private function checkStaffDeleteAccess() {
        if ($this->user_type === 'staff') {
            throw new Exception('Staff members cannot delete payments');
        }
    }
    // ============================================================
    // Module 1: Add Payment Record
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function addPayment($pymt_meth_id, $pymt_stat_id, $pymt_amount_paid, $pymt_date, $appt_id = null) {
        try {
            $validationErrors = $this->validatePaymentData($pymt_meth_id, $pymt_stat_id, $pymt_amount_paid, $pymt_date, $appt_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} 
                      (appt_id, pymt_amount_paid, pymt_meth_id, pymt_date, pymt_stat_id)
                      VALUES (:appt_id, :pymt_amount_paid, :pymt_meth_id, :pymt_date, :pymt_stat_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $stmt->bindParam(':pymt_amount_paid', $pymt_amount_paid);
            $stmt->bindParam(':pymt_meth_id', $pymt_meth_id, PDO::PARAM_INT);
            $stmt->bindParam(':pymt_date', $pymt_date);
            $stmt->bindParam(':pymt_stat_id', $pymt_stat_id, PDO::PARAM_INT);
            $stmt->execute();

            return $this->response("success", ["pymt_id" => $this->conn->lastInsertId()], "Payment record added successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // ============================================================
    // Module 2: View All Payments 
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function getAllPayments() {
        try {
            $query = "SELECT 
                        p.pymt_id,
                        p.pymt_meth_id,     
                        p.pymt_stat_id,    
                        CONCAT(pt.pat_first_name, ' ', pt.pat_last_name) AS patient_name,
                        pm.pymt_meth_name AS payment_method,
                        ps.pymt_stat_name AS payment_status,
                        p.pymt_amount_paid,
                        p.pymt_date,
                        p.appt_id,
                        a.appt_date
                      FROM {$this->table} p
                      LEFT JOIN appointment a ON p.appt_id = a.appt_id
                      LEFT JOIN patient pt ON a.pat_id = pt.pat_id
                      LEFT JOIN payment_method pm ON p.pymt_meth_id = pm.pymt_meth_id
                      LEFT JOIN payment_status ps ON p.pymt_stat_id = ps.pymt_stat_id
                      ORDER BY p.pymt_created_at DESC";

            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Payments retrieved successfully." : "No payments found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 3: View Payment Details by ID
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function getPaymentById($pymt_id) {
        try {
            if (empty($pymt_id) || !is_numeric($pymt_id)) {
                return $this->response("error", [], "Valid payment ID is required.");
            }

            $query = "SELECT 
                        p.pymt_id,
                        p.pymt_meth_id,       
                        p.pymt_stat_id,    
                        CONCAT(pt.pat_first_name, ' ', pt.pat_last_name) AS patient_name,
                        pm.pymt_meth_name AS payment_method,
                        ps.pymt_stat_name AS payment_status,
                        p.pymt_amount_paid,
                        p.pymt_date,
                        p.appt_id
                      FROM {$this->table} p
                      LEFT JOIN appointment a ON p.appt_id = a.appt_id
                      LEFT JOIN patient pt ON a.pat_id = pt.pat_id
                      LEFT JOIN payment_method pm ON p.pymt_meth_id = pm.pymt_meth_id
                      LEFT JOIN payment_status ps ON p.pymt_stat_id = ps.pymt_stat_id
                      WHERE p.pymt_id = :pymt_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_id', $pymt_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Payment record retrieved successfully." : "Payment record not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 4: Update Payment Details
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function updatePayment($pymt_id, $pymt_meth_id, $pymt_stat_id, $pymt_amount_paid, $pymt_date, $appt_id = null) {
        try {
            $validationErrors = $this->validatePaymentData($pymt_meth_id, $pymt_stat_id, $pymt_amount_paid, $pymt_date, $appt_id, $pymt_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if record exists
            $check = $this->conn->prepare("SELECT pymt_id FROM {$this->table} WHERE pymt_id = :pymt_id");
            $check->bindParam(':pymt_id', $pymt_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No payment record found with that ID.");
            }

            $query = "UPDATE {$this->table}
                      SET pymt_meth_id = :pymt_meth_id,
                          pymt_stat_id = :pymt_stat_id,
                          pymt_amount_paid = :pymt_amount_paid,
                          pymt_date = :pymt_date,
                          appt_id = :appt_id,
                          pymt_updated_at = NOW()
                      WHERE pymt_id = :pymt_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_meth_id', $pymt_meth_id, PDO::PARAM_INT);
            $stmt->bindParam(':pymt_stat_id', $pymt_stat_id, PDO::PARAM_INT);
            $stmt->bindParam(':pymt_amount_paid', $pymt_amount_paid);
            $stmt->bindParam(':pymt_date', $pymt_date);
            $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $stmt->bindParam(':pymt_id', $pymt_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Payment record updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to payment record.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 5: Delete Payment Record
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function deletePayment($pymt_id) {
        try {
              $this->checkStaffDeleteAccess();
            if (empty($pymt_id) || !is_numeric($pymt_id)) {
                return $this->response("error", [], "Valid payment ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE pymt_id = :pymt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_id', $pymt_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Payment record deleted successfully.");
            } else {
                return $this->response("info", [], "No payment record found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Add to your Payment class
    public function updatePaymentStatusOnly($pymt_id, $pymt_stat_id) {
        try {
            $query = "UPDATE {$this->table} SET pymt_stat_id = :pymt_stat_id WHERE pymt_id = :pymt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_id', $pymt_id);
            $stmt->bindParam(':pymt_stat_id', $pymt_stat_id);
            
            if ($stmt->execute()) {
                return ['status' => 'success', 'message' => 'Payment status updated successfully'];
            } else {
                return ['status' => 'error', 'message' => 'Failed to update payment status'];
            }
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updatePaymentMethodOnly($pymt_id, $pymt_meth_id) {
        try {
            $query = "UPDATE {$this->table} SET pymt_meth_id = :pymt_meth_id WHERE pymt_id = :pymt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_id', $pymt_id);
            $stmt->bindParam(':pymt_meth_id', $pymt_meth_id);
            
            if ($stmt->execute()) {
                return ['status' => 'success', 'message' => 'Payment method updated successfully'];
            } else {
                return ['status' => 'error', 'message' => 'Failed to update payment method'];
            }
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>