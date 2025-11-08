<?php
class Staff {
    private $conn;
    private $table = "staff";

    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation for staff data
    private function validateStaffData($staff_first_name, $staff_last_name, $staff_email, $staff_id = null) {
        $errors = [];

        if (empty(trim($staff_first_name))) {
            $errors[] = "First name is required.";
        }
        if (empty(trim($staff_last_name))) {
            $errors[] = "Last name is required.";
        }
        if (empty(trim($staff_email)) || !filter_var($staff_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }

        if ($staff_id !== null && (!is_numeric($staff_id) || $staff_id <= 0)) {
            $errors[] = "Valid staff ID is required.";
        }

        return $errors;
    }

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // =====================================
    // Module 1: Add new staff 
    // Accessibility: Super Admin and Staff *
    // =====================================
    public function addStaff($staff_first_name, $staff_last_name, $staff_middle_init, $staff_contact_num, $staff_email) {
        try {
            $validationErrors = $this->validateStaffData($staff_first_name, $staff_last_name, $staff_email);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} 
                      (staff_first_name, staff_last_name, staff_middle_init, staff_contact_num, staff_email)
                      VALUES (:staff_first_name, :staff_last_name, :staff_middle_init, :staff_contact_num, :staff_email)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':staff_first_name', $staff_first_name);
            $stmt->bindParam(':staff_last_name', $staff_last_name);
            $stmt->bindParam(':staff_middle_init', $staff_middle_init);
            $stmt->bindParam(':staff_contact_num', $staff_contact_num);
            $stmt->bindParam(':staff_email', $staff_email);

            if ($stmt->execute()) {
                return $this->response("success", ["staff_id" => $this->conn->lastInsertId()], "Staff added successfully.");
            }
            return $this->response("info", [], "Failed to add staff.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // =====================================
    // Module 2: View all staff 
    // Accessibility: Super Admin and Staff *
    // =====================================
    public function getAllStaff() {
        try {
            $query = "SELECT staff_id, staff_first_name, staff_last_name, staff_middle_init, staff_contact_num, staff_email
                      FROM {$this->table} ORDER BY staff_created_at DESC, staff_id DESC";

            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Staff list retrieved successfully." : "No staff found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 3: View staff by name 
    // Accessibility: Super Admin and Staff *
    // =====================================
    public function searchStaffByName($name) {
        try {
            if (empty(trim($name))) {
                return $this->response("error", [], "Search term is required.");
            }

            $query = "SELECT staff_id, staff_first_name, staff_last_name, staff_middle_init, staff_contact_num, staff_email
                      FROM {$this->table}
                      WHERE staff_first_name LIKE :name OR staff_last_name LIKE :name
                      ORDER BY staff_id DESC";

            $stmt = $this->conn->prepare($query);
            $search = "%$name%";
            $stmt->bindParam(':name', $search);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Matching staff found." : "No staff found matching '$name'."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 4: Update staff 
    // Accessibility: Super Admin and Staff *
    // =====================================
    public function updateStaff($staff_id, $staff_first_name, $staff_last_name, $staff_middle_init, $staff_contact_num, $staff_email) {
        try {
            $validationErrors = $this->validateStaffData($staff_first_name, $staff_last_name, $staff_email, $staff_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if staff exists
            $check = $this->conn->prepare("SELECT staff_id FROM {$this->table} WHERE staff_id = :staff_id");
            $check->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No staff found with that ID.");
            }

            $query = "UPDATE {$this->table} SET 
                        staff_first_name = :staff_first_name,
                        staff_last_name = :staff_last_name,
                        staff_middle_init = :staff_middle_init,
                        staff_contact_num = :staff_contact_num,
                        staff_email = :staff_email,
                        staff_updated_at = NOW()
                      WHERE staff_id = :staff_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':staff_first_name', $staff_first_name);
            $stmt->bindParam(':staff_last_name', $staff_last_name);
            $stmt->bindParam(':staff_middle_init', $staff_middle_init);
            $stmt->bindParam(':staff_contact_num', $staff_contact_num);
            $stmt->bindParam(':staff_email', $staff_email);
            $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Staff updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to staff record.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 5: Delete staff 
    // Accessibility: Super Admin only *
    // =====================================
    public function deleteStaff($staff_id) {
        try {
            if (empty($staff_id) || !is_numeric($staff_id)) {
                return $this->response("error", [], "Valid staff ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE staff_id = :staff_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Staff deleted successfully.");
            } else {
                return $this->response("info", [], "No staff found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 6: Get staff by ID
    // Accessibility: Super Admin and Staff *
    // =====================================
    public function getStaffById($staff_id) {
        try {
            if (empty($staff_id) || !is_numeric($staff_id)) {
                return $this->response("error", [], "Valid staff ID is required.");
            }

            $query = "SELECT staff_id, staff_first_name, staff_last_name, staff_middle_init, staff_contact_num, staff_email
                      FROM {$this->table} WHERE staff_id = :staff_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Staff retrieved successfully." : "Staff not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }
}
?>