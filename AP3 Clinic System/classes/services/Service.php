<?php
class Service {
    private $conn;
    private $user_type;
    private $user_id;
    private $table = "service";

    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }

    // Validation method
    private function validateServiceData($service_name, $service_id = null) {
        $errors = [];

        if (empty(trim($service_name))) {
            $errors[] = "Service name is required";
        } elseif (strlen(trim($service_name)) < 3) {
            $errors[] = "Service name must be at least 3 characters long";
        }

        if ($service_id !== null && (!is_numeric($service_id) || $service_id <= 0)) {
            $errors[] = "Valid service ID is required";
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
            throw new Exception('Staff members cannot delete services');
        }
    }

    // Module 1: Add new service | Superadmin, Staff *
    public function addService($serv_name, $serv_description = null, $serv_price = null) {
        try {
            $validationErrors = $this->validateServiceData($serv_name);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if service already exists
            $check = $this->conn->prepare("SELECT serv_id FROM {$this->table} WHERE serv_name = :serv_name");
            $check->bindParam(':serv_name', $serv_name);
            $check->execute();
            if ($check->rowCount() > 0) {
                return $this->response("info", [], "Service already exists.");
            }

            $query = "INSERT INTO {$this->table} (serv_name, serv_description, serv_price)
                      VALUES (:serv_name, :serv_description, :serv_price)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':serv_name', $serv_name);
            $stmt->bindParam(':serv_description', $serv_description);
            $stmt->bindParam(':serv_price', $serv_price);

            if ($stmt->execute()) {
                return $this->response("success", ["serv_id" => $this->conn->lastInsertId()], "Service added successfully.");
            }
            return $this->response("info", [], "Failed to add service.");
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 2: View all services | Superadmin, Staff *
    public function getAllServices() {
        try {
            $query = "SELECT serv_id, serv_name, serv_description, serv_price FROM {$this->table} ORDER BY serv_created_at DESC, serv_id DESC";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Services retrieved successfully." : "No services found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 3: View appointments by service | Superadmin, Staff *
    public function getAppointmentsByService($serv_id) {
        try {
            if (empty($serv_id) || !is_numeric($serv_id)) {
                return $this->response("error", [], "Valid service ID is required.");
            }

            $query = "SELECT 
                        a.appt_id,
                        CONCAT(p.pat_first_name, ' ', p.pat_last_name) AS patient_name,
                        CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name,
                        s.serv_name,
                        a.appt_date,
                        a.appt_time,
                        st.stat_name
                      FROM appointment a
                      INNER JOIN service s ON a.serv_id = s.serv_id
                      INNER JOIN patient p ON a.pat_id = p.pat_id
                      INNER JOIN doctor d ON a.doc_id = d.doc_id
                      INNER JOIN status st ON a.stat_id = st.stat_id
                      WHERE a.serv_id = :serv_id
                      ORDER BY a.appt_date DESC, a.appt_time ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':serv_id', $serv_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Appointments for this service retrieved successfully." : "No appointments found for this service."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 4: Update service | Superadmin, Staff *
    public function updateService($serv_id, $serv_name, $serv_description = null, $serv_price = null) {
        try {
            $validationErrors = $this->validateServiceData($serv_name, $serv_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if service exists
            $check = $this->conn->prepare("SELECT serv_id FROM {$this->table} WHERE serv_id = :serv_id");
            $check->bindParam(':serv_id', $serv_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No service found with that ID.");
            }

            $query = "UPDATE {$this->table}
                      SET serv_name = :serv_name,
                          serv_description = :serv_description,
                          serv_price = :serv_price,
                          serv_updated_at = NOW()
                      WHERE serv_id = :serv_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':serv_name', $serv_name);
            $stmt->bindParam(':serv_description', $serv_description);
            $stmt->bindParam(':serv_price', $serv_price);
            $stmt->bindParam(':serv_id', $serv_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Service updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to service.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 5: Delete service | Superadmin *
    public function deleteService($serv_id) {
        try {
            $this->checkStaffDeleteAccess();
            if (empty($serv_id) || !is_numeric($serv_id)) {
                return $this->response("error", [], "Valid service ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE serv_id = :serv_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':serv_id', $serv_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Service deleted successfully.");
            } else {
                return $this->response("info", [], "No service found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Supplementary method 
        public function getServicesWithAppointmentCounts($limit = null) {
        try {
            $query = "
                SELECT 
                    s.serv_id,
                    s.serv_name,
                    s.serv_description,
                    s.serv_price,
                    COUNT(a.appt_id) AS total_appointments
                FROM service s
                LEFT JOIN appointment a ON s.serv_id = a.serv_id
                GROUP BY s.serv_id, s.serv_name, s.serv_description, s.serv_price
                ORDER BY s.serv_id DESC
                LIMIT :limit
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['status' => 'success', 'data' => $data];
        } catch (PDOException $e) {
            error_log("PDO Exception in getServicesWithAppointmentCounts: " . $e->getMessage());
            return ['status' => 'error', 'data' => []];
        }
    }

    // Supplementary method
        public function getServiceById($serv_id) {
        try {
            $query = "SELECT serv_id, serv_name, serv_description, serv_price 
                    FROM {$this->table} 
                    WHERE serv_id = :serv_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':serv_id', $serv_id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Service retrieved successfully." : "Service not found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

}
?>
