<?php
class PaymentStatus {
    private $conn;
    private $user_type;
    private $user_id;
    private $table = "payment_status";

    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation method
    private function validatePaymentStatusData($status_name, $id = null) {
    $errors = [];

    if (empty(trim($status_name))) {
        $errors[] = "Payment status name is required.";
    } else {
        $query = "SELECT COUNT(*) FROM payment_status WHERE pymt_stat_name = :status_name";
        if ($id !== null) {
            $query .= " AND pymt_stat_id != :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status_name', $status_name);
        if ($id !== null) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Payment status already exists.";
        }
    }

    // 3. Validate ID if provided
        if ($id !== null && (!is_numeric($id) || $id <= 0)) {
            $errors[] = "Valid payment status ID is required.";
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
            throw new Exception('Staff members cannot delete payments statuses.');
        }
    }

    // ============================================================
    // Module 1: Add Payment Status
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function addPaymentStatus($status_name) {
        try {
            $validationErrors = $this->validatePaymentStatusData($status_name);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} (pymt_stat_name)
                      VALUES (:status_name)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status_name', $status_name);
            $stmt->execute();

            return $this->response("success", ["pymt_stat_id" => $this->conn->lastInsertId()], "Payment status added successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // ============================================================
    // Module 2: View All Payment Status
    // Accessibility: Super Admin and Staff * 
    // ============================================================
    public function getAllPaymentStatus() {
        try {
            $query = "SELECT pymt_stat_id, pymt_stat_name
                      FROM {$this->table}
                      ORDER BY pymt_stat_id ASC";

            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Payment statuses retrieved successfully." : "No payment statuses found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 3: View Payment Status by ID
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function getPaymentStatusById($pymt_stat_id) {
        try {
            if (empty($pymt_stat_id) || !is_numeric($pymt_stat_id)) {
                return $this->response("error", [], "Valid payment status ID is required.");
            }

            $query = "SELECT pymt_stat_id, pymt_stat_name
                      FROM {$this->table}
                      WHERE pymt_stat_id = :pymt_stat_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_stat_id', $pymt_stat_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Payment status retrieved successfully." : "Payment status not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 4: Update Payment Status
    // Accessibility: Super Admin and Staff *
    // ============================================================
    public function updatePaymentStatus($pymt_stat_id, $status_name) {
        try {
            $validationErrors = $this->validatePaymentStatusData($status_name, $pymt_stat_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if record exists
            $check = $this->conn->prepare("SELECT pymt_stat_id FROM {$this->table} WHERE pymt_stat_id = :pymt_stat_id");
            $check->bindParam(':pymt_stat_id', $pymt_stat_id, PDO::PARAM_INT);
            $check->execute();

            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No payment status found with that ID.");
            }

            $query = "UPDATE {$this->table}
                      SET pymt_stat_name = :status_name,
                          pymt_stat_updated_at = NOW()
                      WHERE pymt_stat_id = :pymt_stat_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status_name', $status_name);
            $stmt->bindParam(':pymt_stat_id', $pymt_stat_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Payment status updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to payment status.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // ============================================================
    // Module 5: Delete Payment Status
    // Accessibility: Super Admin Only *
    // ============================================================
    public function deletePaymentStatus($pymt_stat_id) {
        try {
            $this->checkStaffDeleteAccess();
            if (empty($pymt_stat_id) || !is_numeric($pymt_stat_id)) {
                return $this->response("error", [], "Valid payment status ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE pymt_stat_id = :pymt_stat_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pymt_stat_id', $pymt_stat_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Payment status deleted successfully.");
            } else {
                return $this->response("info", [], "No payment status found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }
}
?>
