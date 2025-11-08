<?php 
class Status {
    private $conn;
    private $user_type;
    private $user_id;
    private $table = "status";


    // Common response format 
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation method 
   private function validateStatusData($stat_name, $stat_id = null) {
    $errors = [];

    if (empty(trim($stat_name))) {
        $errors[] = "Status name is required.";
    } else {
        $query = "SELECT COUNT(*) FROM status WHERE stat_name = :stat_name";
        if ($stat_id !== null) {
            $query .= " AND stat_id != :stat_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':stat_name', $stat_name);
        if ($stat_id !== null) {
            $stmt->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);
        }
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
                $errors[] = "Status name already exists.";
            }
        }

        if ($stat_id !== null && (!is_numeric($stat_id) || $stat_id <= 0)) {
            $errors[] = "Valid status ID is required.";
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
            throw new Exception('Staff members cannot delete statuses');
        }
    }

    // Module 1: Add new status | Superadmin, Staff  *
    public function addStatus($stat_name) {
        try {
            $validationErrors = $this->validateStatusData($stat_name);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if status already exists
            $check = $this->conn->prepare("SELECT stat_id FROM {$this->table} WHERE stat_name = :stat_name");
            $check->bindParam(':stat_name', $stat_name);
            $check->execute();
            if ($check->rowCount() > 0) {
                return $this->response("info", [], "Status already exists.");
            }

            $query = "INSERT INTO {$this->table} (stat_name) VALUES (:stat_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':stat_name', $stat_name);

            if ($stmt->execute()) {
                return $this->response("success", ["stat_id" => $this->conn->lastInsertId()], "Status added successfully.");
            }
            return $this->response("info", [], "Failed to add status.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 2: View all statuses | Superadmin, Staff *
    public function getAllStatuses() {
        try {
            $query = "SELECT stat_id, stat_name FROM {$this->table} ORDER BY stat_id ASC";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Statuses retrieved successfully." : "No statuses found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 3: Update Status | Superadmin, Staff *
    public function updateStatus($stat_id, $stat_name) {
        try {
            $validationErrors = $this->validateStatusData($stat_name, $stat_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if status exists
            $check = $this->conn->prepare("SELECT stat_id FROM {$this->table} WHERE stat_id = :stat_id");
            $check->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No status found with that ID.");
            }

            $query = "UPDATE {$this->table} 
                      SET stat_name = :stat_name, stat_updated_at = NOW() 
                      WHERE stat_id = :stat_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':stat_name', $stat_name);
            $stmt->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Status updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to status.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 4: Delete Status | Superadmin *
    public function deleteStatus($stat_id) {
        try {
            $this->checkStaffDeleteAccess(); 
            if (empty($stat_id) || !is_numeric($stat_id)) {
                return $this->response("error", [], "Valid status ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE stat_id = :stat_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Status deleted successfully.");
            } else {
                return $this->response("info", [], "No status found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Supplementary
    public function getStatusById($id) {
    try {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE stat_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->response('success', $result) : $this->response('error', [], 'Status not found');
    } catch (PDOException $e) {
        return $this->response('error', [], $e->getMessage());
    }
}

}
?>