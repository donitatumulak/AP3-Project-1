<?php
class Appointment {
    private $conn;
    private $table = "appointment";
     private $user_type;
    private $user_id;
    
    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }
    
    // Validation method
    private function validateAppointmentData($appt_date, $appt_time, $pat_id, $doc_id, $serv_id, $stat_id) {
        $errors = [];
        
        if (empty($appt_date)) {
            $errors[] = "Appointment date is required";
        } elseif (strtotime($appt_date) === false) {
            $errors[] = "Invalid appointment date format";
        }

        if (empty($appt_time)) {
            $errors[] = "Appointment time is required";
        }
        
        if (empty($pat_id) || !is_numeric($pat_id)) {
            $errors[] = "Valid patient ID is required";
        }
        
        if (empty($doc_id) || !is_numeric($doc_id)) {
            $errors[] = "Valid doctor ID is required";
        }
        
        if (empty($serv_id) || !is_numeric($serv_id)) {
            $errors[] = "Valid service ID is required";
        }
        
        if (empty($stat_id) || !is_numeric($stat_id)) {
            $errors[] = "Valid status ID is required";
        }
        
        return $errors;
    }
    
    // Format appointment ID for display (Year-Month-Sequence)
        public function formatAppointmentId($appt_id, $appt_date) {
        // Safely parse the appointment date
        $date = new DateTime($appt_date);

        // Extract year and month from the appointment date
        $year = $date->format('Y');
        $month = $date->format('m');

        // Pad the appointment ID to 7 digits
        $formatted_sequence = str_pad($appt_id, 7, '0', STR_PAD_LEFT);

        // Return formatted ID like 2025-10-0000123
        return "{$year}-{$month}-{$formatted_sequence}";
    }


    public function __construct($db) {
        $this->conn = $db;
        $this->user_type = $_SESSION['user_type'] ?? '';
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

     private function checkStaffAccess() {
        if ($this->user_type === 'staff') {
            throw new Exception('Staff members cannot access appointment operations');
        }
    }

    // Module 1: Create appointment *
    public function addAppointment($appt_date, $appt_time, $pat_id, $doc_id, $serv_id, $stat_id) {
        try {

            $this->checkStaffAccess();
            // Validate input
            $validationErrors = $this->validateAppointmentData($appt_date, $appt_time, $pat_id, $doc_id, $serv_id, $stat_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Let database handle auto-increment for appt_id
            $query = "INSERT INTO {$this->table} 
                        (appt_date, appt_time, pat_id, doc_id, serv_id, stat_id)
                      VALUES 
                        (:appt_date, :appt_time, :pat_id, :doc_id, :serv_id, :stat_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_date', $appt_date);
            $stmt->bindParam(':appt_time', $appt_time);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->bindParam(':serv_id', $serv_id, PDO::PARAM_INT);
            $stmt->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $appt_id = $this->conn->lastInsertId();
                $formatted_id = $this->formatAppointmentId($appt_id, $appt_date);
                
                return $this->response("success", 
                    [
                        "appt_id" => $appt_id,
                        "formatted_id" => $formatted_id
                    ], 
                    "Appointment created successfully. Your appointment ID: {$formatted_id}"
                );
            } else {
                return $this->response("info", [], "Failed to create appointment.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 2: Search appointment by ID *
    public function getAppointmentById($appt_id) {
        try {

            $this->checkStaffAccess();
            if (empty($appt_id) || !is_numeric($appt_id)) {
                return $this->response("error", [], "Valid appointment ID is required.");
            }

            $query = "SELECT 
                        a.appt_id, 
                        a.appt_date, 
                        a.appt_time,
                        a.pat_id,  
                        a.doc_id,  
                        a.serv_id,
                        CONCAT(p.pat_first_name, ' ', p.pat_last_name) AS patient_name,
                        CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name,
                        s.serv_name AS service_name,
                        st.stat_name AS status_name
                      FROM {$this->table} a
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN doctor d ON a.doc_id = d.doc_id
                      INNER JOIN service s ON a.serv_id = s.serv_id
                      INNER JOIN status st ON a.stat_id = st.stat_id
                      WHERE a.appt_id = :appt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Add formatted ID for display
                $result['formatted_id'] = $this->formatAppointmentId($result['appt_id'], $result['appt_date']);
            }

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Appointment found." : "No appointment found with that ID."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 3: Update appointment (date, doctor, service) *
    public function updateAppointment($appt_id, $appt_time, $appt_date, $doc_id, $serv_id, $pat_id, $stat_id) {
        try {

            $this->checkStaffAccess();
            // Validate input (use dummy stat_id for validation since we're not updating status here)
            $validationErrors = $this->validateAppointmentData($appt_date, $appt_time, $pat_id, $doc_id, $serv_id, $stat_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if appointment exists
            $checkQuery = "SELECT appt_id FROM {$this->table} WHERE appt_id = :appt_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return $this->response("info", [], "No appointment found with the specified ID.");
            }

            $query = "UPDATE {$this->table}
                      SET 
                        appt_date = :appt_date,
                        appt_time = :appt_time,
                        doc_id = :doc_id,
                        pat_id = :pat_id,
                        serv_id = :serv_id,
                        stat_id = :stat_id,
                        appt_updated_at = NOW()
                      WHERE appt_id = :appt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_date', $appt_date);
            $stmt->bindParam(':appt_time', $appt_time);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->bindParam(':serv_id', $serv_id, PDO::PARAM_INT);
            $stmt->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);
            $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Appointment updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to appointment.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 4: Cancel appointment (soft delete by updating status) *
    public function cancelAppointment($appt_id) {
        try {

            $this->checkStaffAccess();

            if (empty($appt_id) || !is_numeric($appt_id)) {
                return $this->response("error", [], "Valid appointment ID is required.");
            }

            // Check if appointment exists
            $checkQuery = "SELECT appt_id FROM {$this->table} WHERE appt_id = :appt_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return $this->response("info", [], "No appointment found with the specified ID.");
            }

            // Update status to cancelled (assuming status ID 3 = cancelled)
            $query = "UPDATE {$this->table}
                      SET 
                        stat_id = 3,
                        appt_updated_at = NOW()
                      WHERE appt_id = :appt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Appointment cancelled successfully.");
            } else {
                return $this->response("info", [], "Appointment was already cancelled or no changes made.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 5: Update appointment status *
    public function updateAppointmentStatus($appt_id, $stat_id) {
        try {

            $this->checkStaffAccess();

            if (empty($appt_id) || !is_numeric($appt_id)) {
                return $this->response("error", [], "Valid appointment ID is required.");
            }

            if (empty($stat_id) || !is_numeric($stat_id)) {
                return $this->response("error", [], "Valid status ID is required.");
            }

            // Check if appointment exists
            $checkQuery = "SELECT appt_id FROM {$this->table} WHERE appt_id = :appt_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return $this->response("info", [], "No appointment found with the specified ID.");
            }

            $query = "UPDATE {$this->table}
                      SET 
                        stat_id = :stat_id,
                        appt_updated_at = NOW()
                      WHERE appt_id = :appt_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':stat_id', $stat_id, PDO::PARAM_INT);
            $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Appointment status updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to appointment status.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

     // Get total appointments count (suppplementary)
    public function getTotalAppointments() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentAppointments($limit = null) {
    $query = "
        SELECT 
            a.appt_id,
            p.pat_first_name, 
            p.pat_last_name,
            d.doc_first_name,
            d.doc_last_name,
            a.appt_date,
            a.appt_time,
            s.stat_name
        FROM " . $this->table . " a
        LEFT JOIN patient p ON a.pat_id = p.pat_id
        LEFT JOIN doctor d ON a.doc_id = d.doc_id
        LEFT JOIN status s ON a.stat_id = s.stat_id
        ORDER BY a.appt_date DESC, a.appt_time DESC
    ";
    
    if ($limit) {
        $query .= " LIMIT :limit";
    }
    
    $stmt = $this->conn->prepare($query);
    if ($limit) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Get all patients for dropdown (supplementary)
    public function getAllPatients() {
        try {
            $query = "SELECT pat_id, pat_first_name, pat_last_name FROM patient ORDER BY pat_last_name, pat_first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->response("success", $patients, "Patients retrieved successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Get all doctors for dropdown (supplementary)
    public function getAllDoctors() {
        try {
            $query = "SELECT doc_id, doc_first_name, doc_last_name FROM doctor ORDER BY doc_last_name, doc_first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->response("success", $doctors, "Doctors retrieved successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Get all services for dropdown (supplementary)
    public function getAllServices() {
        try {
            $query = "SELECT serv_id, serv_name FROM service ORDER BY serv_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->response("success", $services, "Services retrieved successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Get all statuses for dropdown (supplementary)
    public function getAllStatuses() {
        try {
            $query = "SELECT stat_id, stat_name FROM status ORDER BY stat_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->response("success", $statuses, "Statuses retrieved successfully.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Add these methods to your Appointment class
    public function searchDoctorAppointments($doctorName) {
    try {
        $query = "SELECT a.*, 
                         p.pat_first_name, p.pat_last_name,
                         d.doc_first_name, d.doc_last_name,
                         s.serv_name,
                         st.stat_name
                  FROM appointment a
                  LEFT JOIN patient p ON a.pat_id = p.pat_id
                  LEFT JOIN doctor d ON a.doc_id = d.doc_id
                  LEFT JOIN service s ON a.serv_id = s.serv_id
                  LEFT JOIN status st ON a.stat_id = st.stat_id
                  WHERE d.doc_first_name LIKE :name OR d.doc_last_name LIKE :name
                  ORDER BY a.appt_date DESC, a.appt_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $search = "%$doctorName%";
        $stmt->bindParam(':name', $search);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add formatted appointment IDs to each result
        if ($result) {
            foreach ($result as &$appt) {
                $appt['formatted_appt_id'] = $this->formatAppointmentId($appt['appt_id'], $appt['appt_date']);
            }
        }
        
        return $this->response(
            "success",
            $result ?: [],
            $result ? "Doctor appointments found." : "No appointments found for this doctor."
        );
    } catch (PDOException $e) {
        return $this->response("error", [], $e->getMessage());
    }
}

public function getAppointmentByDocId($appt_id) {
    $query = "SELECT a.*,
                     p.pat_first_name, p.pat_last_name,
                     d.doc_first_name, d.doc_last_name,
                     s.serv_name,
                     st.stat_name
              FROM appointment a
              LEFT JOIN patient p ON a.pat_id = p.pat_id
              LEFT JOIN doctor d ON a.doc_id = d.doc_id
              LEFT JOIN service s ON a.serv_id = s.serv_id
              LEFT JOIN status st ON a.stat_id = st.stat_id
              WHERE a.appt_id = :appt_id
              LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':appt_id', $appt_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return ['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)];
    } else {
        return ['status' => 'error', 'message' => 'Appointment not found.'];
    }
}

}
?>