<?php
class MedicalRecord {
    private $conn;
    private $user_type;
    private $user_id;
    private $table = "medical_record";

    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation method
    private function validateMedicalRecordData($appt_id, $med_rec_diagnosis, $med_rec_prescription, $med_rec_visit_date, $med_rec_id = null) {
        $errors = [];

        if (empty($appt_id) || !is_numeric($appt_id)) {
            $errors[] = "Valid appointment ID is required.";
        }
        if (empty(trim($med_rec_diagnosis))) {
            $errors[] = "Diagnosis is required.";
        }
        if (empty(trim($med_rec_prescription))) {
            $errors[] = "Prescription details are required.";
        }
        if (empty(trim($med_rec_visit_date))) {
            $errors[] = "Visit date is required.";
        }

        if ($med_rec_id !== null && (!is_numeric($med_rec_id) || $med_rec_id <= 0)) {
            $errors[] = "Valid medical record ID is required.";
        }

        return $errors;
    }

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
        $this->user_type = $_SESSION['user_type'] ?? '';
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

    private function checkStaffAccess() {
        if ($this->user_type === 'staff') {
            throw new Exception('Staff members cannot perform medical record operations');
        }
    }

    // =====================================
    // Module 1: Create new medical record 
    // Accessibility: Super Admin and Doctor *
    // =====================================
    public function addMedicalRecord($appt_id, $med_rec_diagnosis, $med_rec_prescription, $med_rec_visit_date) {
        try {
              $this->checkStaffAccess();
            $validationErrors = $this->validateMedicalRecordData($appt_id, $med_rec_diagnosis, $med_rec_prescription, $med_rec_visit_date);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            $query = "INSERT INTO {$this->table} 
                      (appt_id, med_rec_diagnosis, med_rec_prescription, med_rec_visit_date)
                      VALUES (:appt_id, :med_rec_diagnosis, :med_rec_prescription, :med_rec_visit_date)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_id', $appt_id);
            $stmt->bindParam(':med_rec_diagnosis', $med_rec_diagnosis);
            $stmt->bindParam(':med_rec_prescription', $med_rec_prescription);
            $stmt->bindParam(':med_rec_visit_date', $med_rec_visit_date);

            if ($stmt->execute()) {
                return $this->response("success", ["med_rec_id" => $this->conn->lastInsertId()], "Medical record added successfully.");
            }
            return $this->response("info", [], "Failed to add medical record.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // =====================================
    // Module 2: View medical records 
    // Accessibility: Super Admin, Staff, and Doctor *
    // =====================================
    public function getAllMedicalRecords() {
        try {
            $query = "SELECT 
                        mr.med_rec_id,
                        mr.appt_id,
                        mr.med_rec_diagnosis,
                        mr.med_rec_prescription,
                        mr.med_rec_visit_date,
                        a.appt_date, 
                        -- Join with appointment and related tables to get patient and doctor info
                        a.pat_id as patient_id,
                        CONCAT(p.pat_first_name, ' ', p.pat_last_name) AS patient_name,
                        a.doc_id as doctor_id,
                        CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name
                      FROM {$this->table} mr
                      INNER JOIN appointment a ON mr.appt_id = a.appt_id
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN doctor d ON a.doc_id = d.doc_id
                      ORDER BY mr.med_rec_created_at DESC";

            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Medical records retrieved successfully." : "No medical records found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 3: View medical record by ID 
    // Accessibility: Super Admin, Staff, and Doctor *
    // =====================================
    public function getMedicalRecordById($med_rec_id) {
        try {
            if (empty($med_rec_id) || !is_numeric($med_rec_id)) {
                return $this->response("error", [], "Valid medical record ID is required.");
            }

            $query = "SELECT 
                        mr.med_rec_id,
                        mr.appt_id,
                        mr.med_rec_diagnosis,
                        mr.med_rec_prescription,
                        mr.med_rec_visit_date,
                        a.appt_date,
                        -- Join with appointment and related tables to get patient and doctor info
                        a.pat_id as patient_id,
                        CONCAT(p.pat_first_name, ' ', p.pat_last_name) AS patient_name,
                        a.doc_id as doctor_id,
                        CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name
                      FROM {$this->table} mr
                      INNER JOIN appointment a ON mr.appt_id = a.appt_id
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN doctor d ON a.doc_id = d.doc_id
                      WHERE mr.med_rec_id = :med_rec_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':med_rec_id', $med_rec_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Medical record retrieved successfully." : "Medical record not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 4: Update medical record 
    // Accessibility: Super Admin and Doctor *
    // =====================================
    public function updateMedicalRecord($med_rec_id, $appt_id, $med_rec_diagnosis, $med_rec_prescription, $med_rec_visit_date) {
        try {
               $this->checkStaffAccess();
            $validationErrors = $this->validateMedicalRecordData($appt_id, $med_rec_diagnosis, $med_rec_prescription, $med_rec_visit_date, $med_rec_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if record exists
            $check = $this->conn->prepare("SELECT med_rec_id FROM {$this->table} WHERE med_rec_id = :med_rec_id");
            $check->bindParam(':med_rec_id', $med_rec_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No medical record found with that ID.");
            }

            $query = "UPDATE {$this->table} SET 
                        appt_id = :appt_id,
                        med_rec_diagnosis = :med_rec_diagnosis,
                        med_rec_prescription = :med_rec_prescription,
                        med_rec_visit_date = :med_rec_visit_date,
                        med_rec_updated_at = NOW()
                      WHERE med_rec_id = :med_rec_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':appt_id', $appt_id);
            $stmt->bindParam(':med_rec_diagnosis', $med_rec_diagnosis);
            $stmt->bindParam(':med_rec_prescription', $med_rec_prescription);
            $stmt->bindParam(':med_rec_visit_date', $med_rec_visit_date);
            $stmt->bindParam(':med_rec_id', $med_rec_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Medical record updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to medical record.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 5: Delete medical record 
    // Accessibility: Super Admin only *
    // =====================================
    public function deleteMedicalRecord($med_rec_id) {
        try {
               $this->checkStaffAccess();
            if (empty($med_rec_id) || !is_numeric($med_rec_id)) {
                return $this->response("error", [], "Valid medical record ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE med_rec_id = :med_rec_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':med_rec_id', $med_rec_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Medical record deleted successfully.");
            } else {
                return $this->response("info", [], "No medical record found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // =====================================
    // Module 6: Get medical records by patient ID
    // Accessibility: Super Admin, Staff, and Doctor *
    // =====================================
    public function getMedicalRecordsByPatientId($pat_id) {
        try {
            if (empty($pat_id) || !is_numeric($pat_id)) {
                return $this->response("error", [], "Valid patient ID is required.");
            }

            $query = "SELECT 
                        mr.med_rec_id,
                        mr.appt_id,
                        mr.med_rec_diagnosis,
                        mr.med_rec_prescription,
                        mr.med_rec_visit_date,
                        mr.med_rec_created_at,
                        CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name
                      FROM {$this->table} mr
                      INNER JOIN appointment a ON mr.appt_id = a.appt_id
                      INNER JOIN doctor d ON a.doc_id = d.doc_id
                      WHERE a.pat_id = :pat_id
                      ORDER BY mr.med_rec_created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Medical records retrieved successfully." : "No medical records found for this patient."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Supplementary method
    public function getMedicalRecordByAppointmentId($appt_id) {
    try {
        if (empty($appt_id) || !is_numeric($appt_id)) {
            return $this->response("error", [], "Valid appointment ID is required.");
        }

        $query = "SELECT 
                    mr.med_rec_id,
                    mr.appt_id,
                    mr.med_rec_diagnosis,
                    mr.med_rec_prescription,
                    mr.med_rec_visit_date,
                    a.pat_id as patient_id,
                    CONCAT(p.pat_first_name, ' ', p.pat_last_name) AS patient_name,
                    a.doc_id as doctor_id,
                    CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name
                  FROM {$this->table} mr
                  INNER JOIN appointment a ON mr.appt_id = a.appt_id
                  INNER JOIN patient p ON a.pat_id = p.pat_id
                  INNER JOIN doctor d ON a.doc_id = d.doc_id
                  WHERE mr.appt_id = :appt_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':appt_id', $appt_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->response(
            "success",
            $result ?: [],
            $result ? "Medical record retrieved successfully." : "No medical record found for this appointment."
        );
    } catch (PDOException $e) {
        return $this->response("error", [], $e->getMessage());
    }
    }

    // ================================
    // Search medical records by patient name
    // ================================
    public function searchMedicalRecordsByPatientName($name, $doc_id) {
        try {
            $search = "%{$name}%";
            $query = "SELECT 
                        mr.med_rec_id,
                        mr.appt_id,
                        mr.med_rec_diagnosis,
                        mr.med_rec_prescription,
                        mr.med_rec_visit_date,
                        a.appt_date,
                        p.pat_first_name,
                        p.pat_last_name,
                        CONCAT(p.pat_first_name, ' ', p.pat_last_name) AS patient_name
                    FROM {$this->table} mr
                    INNER JOIN appointment a ON mr.appt_id = a.appt_id
                    INNER JOIN patient p ON a.pat_id = p.pat_id
                    WHERE a.doc_id = :doc_id
                        AND (p.pat_first_name LIKE :search OR p.pat_last_name LIKE :search)
                    ORDER BY mr.med_rec_created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->bindParam(':search', $search);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->response(
                "success",
                $results ?: [],
                $results ? "Records found." : "No records match that name."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

}
?>