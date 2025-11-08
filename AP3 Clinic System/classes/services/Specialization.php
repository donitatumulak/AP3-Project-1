<?php
class Specialization {
    private $conn;
     private $user_type;
    private $user_id;
    private $table = "specialization";
    
    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }
    
    // Validation method
    private function validateSpecializationData($spec_name, $spec_id = null) {
        $errors = [];
        
        if (empty(trim($spec_name))) {
            $errors[] = "Specialization name is required";
        } elseif (strlen(trim($spec_name)) < 2) {
            $errors[] = "Specialization name must be at least 2 characters long";
        }
        
        if ($spec_id !== null && (empty($spec_id) || !is_numeric($spec_id))) {
            $errors[] = "Valid specialization ID is required";
        }
        
        return $errors;
    }

    public function __construct($db) {
        $this->conn = $db;
        $this->user_type = $_SESSION['user_type'] ?? '';
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

    private function checkStaffAddAccess() {
        if ($this->user_type === 'staff') {
            throw new Exception('Staff members cannot add specializations');
        }
    }

    private function checkStaffDeleteAccess() {
        if ($this->user_type === 'staff') {
            throw new Exception('Staff members cannot delete specializations');
        }
    }

    // Module 1: Add new specialization | Superadmin *
    public function addSpecialization($spec_name) {
        try {
            $this->checkStaffAddAccess();
            // Validate input
            $validationErrors = $this->validateSpecializationData($spec_name);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} (spec_name) VALUES (:spec_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':spec_name', $spec_name);

            if ($stmt->execute()) {
                return $this->response("success", ["spec_id" => $this->conn->lastInsertId()], "Specialization added successfully.");
            }
            return $this->response("info", [], "Failed to add specialization.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 2: View all specializations | Superadmin, Staff *
    public function getAllSpecializations() {
        try {
            $query = "SELECT spec_id, spec_name FROM {$this->table} ORDER BY spec_created_at DESC, spec_id DESC";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Specializations retrieved successfully." : "No specializations found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 3: View specific specialization by ID | Superadmin, Staff *
    public function getSpecializationById($spec_id) {
        try {
            if (empty($spec_id) || !is_numeric($spec_id)) {
                return $this->response("error", [], "Valid specialization ID is required.");
            }

            $query = "SELECT spec_id, spec_name FROM {$this->table} WHERE spec_id = :spec_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Specialization retrieved successfully." : "Specialization not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 4: View all doctors under a specific specialization (by ID) | Superadmin, Staff *
    public function getDoctorsBySpecialization($spec_id) {
        try {
            if (empty($spec_id) || !is_numeric($spec_id)) {
                return $this->response("error", [], "Valid specialization ID is required.");
            }

            $query = "SELECT 
                        d.doc_id, 
                        CONCAT('Dr. ', d.doc_first_name, 
                        IF(d.doc_middle_init IS NOT NULL AND d.doc_middle_init != '', CONCAT(' ', d.doc_middle_init, '. '), ' '),
                        d.doc_last_name) AS doctor_name,
                        s.spec_name
                    FROM doctor d
                    INNER JOIN specialization s ON d.spec_id = s.spec_id
                    WHERE s.spec_id = :spec_id
                    ORDER BY d.doc_last_name, d.doc_first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Doctors retrieved successfully." : "No doctors found for this specialization."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 5: Update specialization | Superadmin, Staff *
    public function updateSpecialization($spec_id, $new_name) {
        try {
            // Validate input
            $validationErrors = $this->validateSpecializationData($new_name, $spec_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if specialization exists
            $checkQuery = "SELECT spec_id FROM {$this->table} WHERE spec_id = :spec_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return $this->response("info", [], "No specialization found with the specified ID.");
            }

            $query = "UPDATE {$this->table}
                    SET spec_name = :new_name
                    WHERE spec_id = :spec_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':new_name', $new_name);
            $stmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Specialization updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to specialization.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 6: Delete specialization | Superadmin *
    public function deleteSpecialization($spec_id) {
        try {
             $this->checkStaffDeleteAccess();
            if (empty($spec_id) || !is_numeric($spec_id)) {
                return $this->response("error", [], "Valid specialization ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE spec_id = :spec_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Specialization deleted successfully.");
            } else {
                return $this->response("info", [], "No specialization found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }
}
?>