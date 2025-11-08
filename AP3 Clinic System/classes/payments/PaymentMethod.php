<?php
class PaymentMethod {
    private $conn;
    private $user_type;
    private $user_id;
    private $table = "payment_method";

    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation method
    private function validatePaymentMethodData($method_name, $id = null) {
    $errors = [];

    // 1. Check for empty or whitespace-only input
    if (empty(trim($method_name))) {
        $errors[] = "Payment method name is required.";
    } else {
        // 2. Check if method already exists in the database (avoid duplicates)
        $query = "SELECT COUNT(*) FROM payment_method WHERE pymt_meth_name = :method_name";
        if ($id !== null) {
            // Exclude current record if updating
            $query .= " AND pymt_meth_id != :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':method_name', $method_name);
            if ($id !== null) {
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Payment method already exists.";
            }
        }

        // 3. Validate ID if provided
        if ($id !== null && (!is_numeric($id) || $id <= 0)) {
            $errors[] = "Valid payment method ID is required.";
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
            throw new Exception('Staff members cannot delete payment methods.');
        }
    }

    // ============================================================
    // Module 1: Add Payment Method
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function addPaymentMethod($method_name) {
        try {
            $validationErrors = $this->validatePaymentMethodData($method_name);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} (pymt_meth_name) 
                      VALUES (:method_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':method_name', $method_name);
            $stmt->execute();

            return $this->response("success", ["pymt_meth_id" => $this->conn->lastInsertId()], "Payment method added successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // ============================================================
    // Module 2: View All Payment Methods
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function getAllPaymentMethods() {
        try {
            $query = "SELECT pymt_meth_id, pymt_meth_name
                      FROM {$this->table}
                      ORDER BY pymt_meth_id ASC";

            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Payment methods retrieved successfully." : "No payment methods found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 3: View Payment Method by ID
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function getPaymentMethodById($pymt_meth_id) {
        try {
            if (empty($pymt_meth_id) || !is_numeric($pymt_meth_id)) {
                return $this->response("error", [], "Valid payment method ID is required.");
            }

            $query = "SELECT pymt_meth_id, pymt_meth_name
                      FROM {$this->table} WHERE pymt_meth_id = :pymt_meth_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_meth_id', $pymt_meth_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Payment method retrieved successfully." : "Payment method not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 4: Update Payment Method
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function updatePaymentMethod($pymt_meth_id, $method_name) {
        try {
            $validationErrors = $this->validatePaymentMethodData($method_name, $pymt_meth_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check existence
            $check = $this->conn->prepare("SELECT pymt_meth_id FROM {$this->table} WHERE pymt_meth_id = :pymt_meth_id");
            $check->bindParam(':pymt_meth_id', $pymt_meth_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No payment method found with that ID.");
            }

            $query = "UPDATE {$this->table}
                      SET pymt_meth_name = :method_name,
                          pymt_meth_updated_at = NOW()
                      WHERE pymt_meth_id = :pymt_meth_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':method_name', $method_name);
            $stmt->bindParam(':pymt_meth_id', $pymt_meth_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Payment method updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to payment method.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 5: Delete Payment Method
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function deletePaymentMethod($pymt_meth_id) {
        try {
            $this->checkStaffDeleteAccess();
            if (empty($pymt_meth_id) || !is_numeric($pymt_meth_id)) {
                return $this->response("error", [], "Valid payment method ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE pymt_meth_id = :pymt_meth_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_meth_id', $pymt_meth_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Payment method deleted successfully.");
            } else {
                return $this->response("info", [], "No payment method found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }
}
?>
