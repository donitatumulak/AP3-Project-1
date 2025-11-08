<?php
class Doctor {
    private $conn;
    private $table = "doctor";
    
    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }
    
    // Validation method
    private function validateDoctorData($first_name, $last_name, $contact_num, $email, $spec_id) {
        $errors = [];
        
        if (empty(trim($first_name))) {
            $errors[] = "First name is required";
        }
        if (empty(trim($last_name))) {
            $errors[] = "Last name is required";
        }
        if (empty($spec_id) || !is_numeric($spec_id)) {
            $errors[] = "Valid specialty ID is required";
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        if (!empty($contact_num) && !preg_match('/^[\d\s\-\(\)\+]+$/', $contact_num)) {
            $errors[] = "Invalid contact number format";
        }
        
        return $errors;
    }

    public function __construct($db) {
        $this->conn = $db;
    }

    // Module 1: Add new doctor | Superadmin, Doctor *
    public function addDoctor($first_name, $last_name, $middle_init, $contact_num, $email, $spec_id) {
        try {
            // Validate input
            $validationErrors = $this->validateDoctorData($first_name, $last_name, $contact_num, $email, $spec_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} 
                        (doc_first_name, doc_last_name, doc_middle_init, doc_contact_num, doc_email, spec_id)
                      VALUES 
                        (:first_name, :last_name, :middle_init, :contact_num, :email, :spec_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':middle_init', $middle_init);
            $stmt->bindParam(':contact_num', $contact_num);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->response("success", ["doc_id" => $this->conn->lastInsertId()], "Doctor added successfully.");
            } else {
                return $this->response("info", [], "Failed to add doctor.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 2: Update doctor information | Superadmin, Doctor *
    public function updateDoctor($doc_id, $first_name, $last_name, $middle_init, $contact_num, $email, $spec_id) {
        try {
            // Validate input
            $validationErrors = $this->validateDoctorData($first_name, $last_name, $contact_num, $email, $spec_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if doctor exists
            $checkQuery = "SELECT doc_id FROM {$this->table} WHERE doc_id = :doc_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return $this->response("info", [], "No doctor found with the specified ID.");
            }

            $query = "UPDATE {$this->table}
                      SET 
                        doc_first_name = :first_name,
                        doc_last_name = :last_name,
                        doc_middle_init = :middle_init,
                        doc_contact_num = :contact_num,
                        doc_email = :email,
                        spec_id = :spec_id,
                        doc_updated_at = NOW()
                      WHERE doc_id = :doc_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':middle_init', $middle_init);
            $stmt->bindParam(':contact_num', $contact_num);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':spec_id', $spec_id, PDO::PARAM_INT);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Doctor updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to doctor information.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 3: View previous appointments | Superadmin, Doctor *
    public function getPreviousAppointments($doc_id, $limit = null) {
        try {
            if (empty($doc_id) || !is_numeric($doc_id)) {
                return $this->response("error", [], "Valid doctor ID is required.");
            }

            $query = "SELECT 
                        a.appt_id,
                        a.appt_date,
                        CONCAT(
                            p.pat_first_name,
                            IF(p.pat_middle_init IS NOT NULL AND p.pat_middle_init != '', CONCAT(' ', LEFT(p.pat_middle_init, 1), '. '), ' '),
                            p.pat_last_name
                        ) AS patient_full_name,
                        s.serv_name AS service_name,
                        st.stat_name AS status_name 
                      FROM appointment a
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN service s ON a.serv_id = s.serv_id
                      INNER JOIN status st ON a.stat_id = st.stat_id 
                      WHERE a.doc_id = :doc_id
                        AND (
                            DATE(a.appt_date) < CURDATE()
                            OR (DATE(a.appt_date) = CURDATE() AND st.stat_name = 'Completed')
                        )
                      ORDER BY a.appt_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result, 
                $result ? "Previous appointments retrieved successfully." : "No previous appointments found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 4: View today's (current) appointments | Superadmin, Doctor *
    public function getTodaysAppointments($doc_id, $limit = null) {
        try {
            if (empty($doc_id) || !is_numeric($doc_id)) {
                return $this->response("error", [], "Valid doctor ID is required.");
            }

            $query = "SELECT 
                        a.appt_id,
                        a.appt_date,
                        CONCAT(
                            p.pat_first_name,
                            IF(p.pat_middle_init IS NOT NULL AND p.pat_middle_init != '', CONCAT(' ', LEFT(p.pat_middle_init, 1), '. '), ' '),
                            p.pat_last_name
                        ) AS patient_full_name,
                        s.serv_name AS service_name,
                        st.stat_name AS status_name
                      FROM appointment a
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN service s ON a.serv_id = s.serv_id
                      INNER JOIN status st ON a.stat_id = st.stat_id
                      WHERE a.doc_id = :doc_id
                        AND DATE(a.appt_date) = CURDATE()
                        AND st.stat_name = 'Scheduled'
                      ORDER BY a.appt_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result, 
                $result ? "Today's appointments retrieved successfully." : "No appointments scheduled for today."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 5: View future appointments | Superadmin, Doctor *
    public function getFutureAppointments($doc_id, $limit = null) {
        try {
            if (empty($doc_id) || !is_numeric($doc_id)) {
                return $this->response("error", [], "Valid doctor ID is required.");
            }

            $query = "SELECT 
                        a.appt_id,
                        a.appt_date,
                        CONCAT(
                            p.pat_first_name,
                            IF(p.pat_middle_init IS NOT NULL AND p.pat_middle_init != '', CONCAT(' ', LEFT(p.pat_middle_init, 1), '. '), ' '),
                            p.pat_last_name
                        ) AS patient_full_name,
                        s.serv_name AS service_name,
                        st.stat_name AS status_name
                      FROM appointment a
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN service s ON a.serv_id = s.serv_id
                      INNER JOIN status st ON a.stat_id = st.stat_id
                      WHERE a.doc_id = :doc_id
                        AND a.appt_date > CURDATE()
                      ORDER BY a.appt_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result, 
                $result ? "Future appointments retrieved successfully." : "No future appointments found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 6: Delete doctor (with ON DELETE CASCADE) | Superadmin *
    public function deleteDoctor($doc_id) {
        try {
            if (empty($doc_id) || !is_numeric($doc_id)) {
                return $this->response("error", [], "Valid doctor ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE doc_id = :doc_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Doctor deleted successfully.");
            } else {
                return $this->response("info", [], "No doctor found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

        // Module 7: Get all doctors with formatted names | Superadmin, Staff (supplementary)
    public function getAllDoctors() {
        try {
            $query = "SELECT 
                        d.doc_id,
                        d.doc_first_name,
                        d.doc_last_name,
                        d.doc_middle_init,
                        CONCAT(
                            d.doc_first_name,
                            IF(d.doc_middle_init IS NOT NULL AND d.doc_middle_init != '', CONCAT(' ', LEFT(d.doc_middle_init, 1), '. '), ' '),
                            d.doc_last_name
                        ) AS full_name,
                        d.doc_contact_num,
                        d.doc_email,
                        d.spec_id,
                        s.spec_name,
                        d.doc_created_at,
                        d.doc_updated_at
                    FROM {$this->table} d
                    LEFT JOIN specialization s ON d.spec_id = s.spec_id
                    ORDER BY doc_created_at DESC, d.doc_id DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Doctors retrieved successfully." : "No doctors found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 8: Get doctor by ID | Superadmin, Staff (supplementary)
    public function getDoctorById($doc_id) {
        try {
            if (empty($doc_id) || !is_numeric($doc_id)) {
                return $this->response("error", [], "Valid doctor ID is required.");
            }

            $query = "SELECT 
                        d.doc_id,
                        d.doc_first_name,
                        d.doc_last_name,
                        d.doc_middle_init,
                        d.doc_contact_num,
                        d.doc_email,
                        d.spec_id,
                        s.spec_name,
                        d.doc_created_at,
                        d.doc_updated_at
                    FROM {$this->table} d
                    LEFT JOIN specialization s ON d.spec_id = s.spec_id
                    WHERE d.doc_id = :doc_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Doctor retrieved successfully." : "Doctor not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Supplementary
    public function getAllSpecializations() {
        try {
            $query = "SELECT spec_id, spec_name FROM specialization ORDER BY spec_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'status' => 'success',
                'data' => $specializations,
                'message' => 'Specializations retrieved successfully.'
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'data' => [],
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}
?>