<?php 
class Patient {
    private $conn;
    private $table = "patient";

    // common response format 
    private function response($status, $data = [], $message = "") {
        return [
            "status"=> $status,
            "data"=> $data,
            "message"=> $message
        ];
    }

    // validation method
    private function validatePatientData($pat_first_name, $pat_last_name, $pat_email, $pat_contact_num, $pat_dob, $pat_id = null) {
        $errors = [];

        if (empty(trim($pat_first_name))) {
            $errors[] = "First name is required";
        }
        if (empty(trim($pat_last_name))) {
            $errors[] = "Last name is required";
        }
        if (empty(trim($pat_email)) || !filter_var($pat_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        if (empty(trim($pat_contact_num))) {
            $errors[] = "Contact number is required";
        }
        if (empty(trim($pat_dob))) {
            $errors[] = "Date of birth is required";
        }

        if ($pat_id !== null && (!is_numeric($pat_id) || $pat_id <= 0)) {
            $errors[] = "Valid patient ID is required";
        }
        return $errors;
    }

    // constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // module 1: Add new patient *
    public function addPatient($pat_first_name, $pat_last_name, $pat_middle_init, $pat_dob, $pat_gender, $pat_contact_num, $pat_email, $pat_address) {
        try {
            $validationErrors = $this->validatePatientData($pat_first_name, $pat_last_name, $pat_email, $pat_contact_num, $pat_dob);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", " , $validationErrors));
            }

            $query = "INSERT INTO {$this->table} 
                    (pat_first_name, pat_last_name, pat_middle_init, pat_dob, pat_gender, pat_contact_num, pat_email, pat_address, pat_created_at, pat_updated_at) 
                    VALUES (:pat_first_name, :pat_last_name, :pat_middle_init, :pat_dob, :pat_gender, :pat_contact_num, :pat_email, :pat_address, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pat_first_name', $pat_first_name);
            $stmt->bindParam(':pat_last_name', $pat_last_name);
            $stmt->bindParam(':pat_middle_init', $pat_middle_init);
            $stmt->bindParam(':pat_dob', $pat_dob);
            $stmt->bindParam(':pat_gender', $pat_gender);
            $stmt->bindParam(':pat_contact_num', $pat_contact_num);
            $stmt->bindParam(':pat_email', $pat_email);
            $stmt->bindParam(':pat_address', $pat_address);

            if ($stmt->execute()) {
                return $this->response("success", ["pat_id" => $this->conn->lastInsertId()], "Patient added successfully.");
            }
            return $this->response("info", [], "Failed to add patient.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: ". $e->getMessage());
        }
    }

    // module 2: view all patients  *
    public function getAllPatient() {
        try {
            $query = "SELECT pat_id, pat_first_name, pat_last_name, pat_middle_init, pat_dob, pat_gender, pat_contact_num, pat_email, pat_address 
                      FROM {$this->table} ORDER BY pat_created_at DESC, pat_id DESC";

            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Patients retrieved successfully." : "No patients found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // module 3: view patient by ID (supplementary)
    public function getPatientByID($pat_id) {
        try {
            if (empty($pat_id) || !is_numeric($pat_id)) {
                return $this->response("error", [], "Valid patient ID is required.");
            }

            $query = "SELECT pat_id, pat_first_name, pat_last_name, pat_middle_init, pat_dob, pat_gender, pat_contact_num, pat_email, pat_address 
                      FROM {$this->table} WHERE pat_id = :pat_id";
        
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC); // Changed to fetch single record

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Patient retrieved successfully." : "Patient not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // module 4: search patient by name *
    public function searchPatientByName($name) {
        try {
            if (empty(trim($name))) {
                return $this->response("error", [], "Search term is required.");
            }

            $query = "SELECT pat_id, pat_first_name, pat_last_name, pat_middle_init, pat_dob, pat_gender, pat_contact_num, pat_email, pat_address 
                      FROM {$this->table} 
                      WHERE pat_first_name LIKE :name OR pat_last_name LIKE :name 
                      ORDER BY pat_id DESC";
            
            $stmt = $this->conn->prepare($query);
            $search = "%$name%";
            $stmt->bindParam(':name', $search);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Matching patients found." : "No patients found matching '$name'."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // module 5: update patient *
    public function updatePatient($pat_id, $pat_first_name, $pat_last_name, $pat_middle_init, $pat_dob, $pat_gender, $pat_contact_num, $pat_email, $pat_address) {
        try {
            $validationErrors = $this->validatePatientData($pat_first_name, $pat_last_name, $pat_email, $pat_contact_num, $pat_dob, $pat_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if patient exists
            $check = $this->conn->prepare("SELECT pat_id FROM {$this->table} WHERE pat_id = :pat_id");
            $check->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No patient found with that ID.");
            }

            $query = "UPDATE {$this->table} SET 
                        pat_first_name = :pat_first_name,
                        pat_last_name = :pat_last_name,
                        pat_middle_init = :pat_middle_init,
                        pat_dob = :pat_dob,
                        pat_gender = :pat_gender,
                        pat_contact_num = :pat_contact_num,
                        pat_email = :pat_email,
                        pat_address = :pat_address,
                        pat_updated_at = NOW()
                      WHERE pat_id = :pat_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pat_first_name', $pat_first_name);
            $stmt->bindParam(':pat_last_name', $pat_last_name);
            $stmt->bindParam(':pat_middle_init', $pat_middle_init);
            $stmt->bindParam(':pat_dob', $pat_dob);
            $stmt->bindParam(':pat_gender', $pat_gender);
            $stmt->bindParam(':pat_contact_num', $pat_contact_num);
            $stmt->bindParam(':pat_email', $pat_email);
            $stmt->bindParam(':pat_address', $pat_address);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Patient updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to patient record.");
            } 
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // module 6: delete patient *
    public function deletePatient($pat_id) {
        try {
            if (empty($pat_id) || !is_numeric($pat_id)) {
                return $this->response("error", [], "Valid patient ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE pat_id = :pat_id";
           
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Patient deleted successfully.");
            } else {
                return $this->response("info", [], "No patient found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Supplementary methods
    public function getAppointmentCounts($patient_id) {
    try {  $query = "
            SELECT 
                SUM(CASE WHEN appt_date >= CURDATE() AND stat_id = '1' THEN 1 ELSE 0 END) as upcoming_count,
                SUM(CASE WHEN stat_id = '2' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN stat_id = '3' THEN 1 ELSE 0 END) as cancelled_count,
                COUNT(DISTINCT CASE WHEN stat_id = '2' THEN doc_id END) as doctors_count
            FROM appointment 
            WHERE pat_id = :pat_id
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pat_id', $patient_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['upcoming_count' => 0, 'completed_count' => 0, 'cancelled_count' => 0, 'doctors_count' => 0];
    }
    }

    public function getUpcomingAppointments($patient_id, $limit = null) {
        $query = "
            SELECT 
                a.appt_id,
                a.appt_date,
                a.appt_time,
                a.stat_id,
                s.serv_name,
                st.stat_name AS status_name,
                CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) as doctor_name
            FROM appointment a
            INNER JOIN doctor d ON a.doc_id = d.doc_id
            INNER JOIN service s ON a.serv_id = s.serv_id
            INNER JOIN status st ON a.stat_id = st.stat_id 
            WHERE a.pat_id = :pat_id 
            AND a.appt_date >= CURDATE()
            AND a.stat_id IN ('1')
            ORDER BY a.appt_date ASC, a.appt_time ASC
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pat_id', $patient_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAppointmentHistory($patient_id, $limit = null) {
    $query = "
        SELECT 
            a.appt_id,
            a.appt_date,
            a.appt_time,
            a.stat_id,
            s.serv_name,
            st.stat_name AS status_name,
            CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) as doctor_name
        FROM appointment a
        INNER JOIN doctor d ON a.doc_id = d.doc_id
        INNER JOIN service s ON a.serv_id = s.serv_id
        INNER JOIN status st ON a.stat_id = st.stat_id 
        WHERE a.pat_id = :pat_id 
        AND (a.appt_date < CURDATE() OR a.stat_id IN ('2', '3'))
        ORDER BY a.appt_date DESC, a.appt_time DESC
    ";

    // 👇 Add LIMIT only if it's not null
    if ($limit !== null) {
        $query .= " LIMIT :limit";
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':pat_id', $patient_id, PDO::PARAM_INT);

    if ($limit !== null) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>