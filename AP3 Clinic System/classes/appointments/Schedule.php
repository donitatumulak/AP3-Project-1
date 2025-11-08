<?php
class Schedule {
    private $conn;
    private $table = "schedule";
    private $user_type;
    private $user_id;

    // common response format
    private function response($s, $d = [], $m = "") {
        return [
            "status"  => $s,
            "data"    => $d,
            "message" => $m
        ];
    }

    // validation method 
    private function validateScheduleData($doc, $days, $start, $end, $id = null) {
        $errors = [];

        if (empty($doc) || !is_numeric($doc)) {
            $errors[] = "Valid doctor ID is required";
        }
        if (empty(trim($days))) {
            $errors[] = "Schedule day is required";
        }
        if (empty(trim($start))) {
            $errors[] = "Start time is required";
        }
        if (empty(trim($end))) {
            $errors[] = "End time is required";
        }

        if ($id !== null && (!is_numeric($id) || $id <= 0)) {
            $errors[] = "Valid schedule ID is required";
        }

        return $errors;
    }

    // constructor 
    public function __construct($db) {
        $this->conn = $db;
        $this->user_type = $_SESSION['user_type'] ?? '';
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

     private function checkStaffAccess() {
        if ($this->user_type === 'staff') {
            throw new Exception('Staff members cannot access schedule operations');
        }
    }

    // module 1: Add new schedule | Superadmin, Doctor *
    public function addSchedule($doc, $days, $start, $end) {
    try {
        $this->checkStaffAccess();

        // Validate input first
        $v = $this->validateScheduleData($doc, $days, $start, $end);
        if (!empty($v)) {
            return $this->response("error", [], implode(", ", $v));
        }

        // Split comma-separated days into array
        $daysArray = array_map('trim', explode(',', $days));
        $insertedCount = 0;

        // Prepare statement once for efficiency
        $sql = "INSERT INTO {$this->table}
                (doc_id, sched_days, sched_start_time, sched_end_time)
                VALUES (:doc, :days, :start, :end)";
        $stmt = $this->conn->prepare($sql);

        foreach ($daysArray as $day) {
            if (!empty($day)) {
                $stmt->bindParam(':doc', $doc, PDO::PARAM_INT);
                $stmt->bindParam(':days', $day);
                $stmt->bindParam(':start', $start);
                $stmt->bindParam(':end', $end);
                if ($stmt->execute()) {
                    $insertedCount++;
                }
            }
        }

        if ($insertedCount > 0) {
            return $this->response("success", [], "Added {$insertedCount} schedule(s).");
        } else {
            return $this->response("error", [], "No valid days selected.");
        }

    } catch (PDOException $e) {
        return $this->response("error", [], $e->getMessage());
    }
}

    // Module 2: View all schedules | Superadmin, Doctor *
    // Accepts optional $doc_id to limit to one doctor
    public function getAllSchedules($doc_id = null) {
        try {
             $this->checkStaffAccess();
            $where = "";
            if (!empty($doc_id) && is_numeric($doc_id)) {
                $where = "WHERE s.doc_id = :doc_id";
            }

            $query = "SELECT s.sched_id,
                            s.doc_id,
                            CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name,
                            s.sched_days,
                            s.sched_start_time,
                            s.sched_end_time
                    FROM {$this->table} s
                    LEFT JOIN doctor d ON s.doc_id = d.doc_id
                    $where
                    ORDER BY s.sched_created_at DESC, d.doc_last_name ASC, FIELD(s.sched_days,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
                            s.sched_start_time ASC";

            $stmt = $this->conn->prepare($query);
            if (!empty($where)) {
                $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Schedules retrieved successfully." : "No schedules found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 3: View today's schedules (by weekday name) | Superadmin, Doctor *
    // Accepts optional $doc_id to limit to one doctor
    public function getTodaySchedules($doc_id = null) {
        try {
             $this->checkStaffAccess();
            // weekday name, e.g. "Monday"
            $todayName = date("l");

            $where = "WHERE s.sched_days = :today";
            if (!empty($doc_id) && is_numeric($doc_id)) {
                $where .= " AND s.doc_id = :doc_id";
            }

            $query = "SELECT s.sched_id,
                            s.doc_id,
                            CONCAT('Dr. ', d.doc_first_name, ' ', d.doc_last_name) AS doctor_name,
                            s.sched_days,
                            s.sched_start_time,
                            s.sched_end_time
                    FROM {$this->table} s
                    LEFT JOIN doctor d ON s.doc_id = d.doc_id
                    $where
                    ORDER BY s.sched_start_time ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':today', $todayName);
            if (!empty($doc_id) && is_numeric($doc_id)) {
                $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success",
                $result ?: [],
                $result ? "Today's schedules retrieved successfully." : "No schedules for today."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }


    // Module 4: Update schedule (by sched_id) *
    public function updateSchedule($sched_id, $doc, $days, $start, $end) {
        try {
             $this->checkStaffAccess();
            $validationErrors = $this->validateScheduleData($doc, $days, $start, $end, $sched_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // check existence
            $check = $this->conn->prepare("SELECT sched_id FROM {$this->table} WHERE sched_id = :sched_id");
            $check->bindParam(':sched_id', $sched_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() === 0) {
                return $this->response("info", [], "No schedule found with that ID.");
            }

            $query = "UPDATE {$this->table}
                      SET doc_id = :doc,
                          sched_days = :days,
                          sched_start_time = :start,
                          sched_end_time = :end,
                          sched_updated_at = NOW()
                      WHERE sched_id = :sched_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc', $doc, PDO::PARAM_INT);
            $stmt->bindParam(':days', $days);
            $stmt->bindParam(':start', $start);
            $stmt->bindParam(':end', $end);
            $stmt->bindParam(':sched_id', $sched_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Schedule updated successfully.");
            } else {
                return $this->response("info", [], "No changes made to schedule.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 5: Delete schedule  *
    public function deleteSchedule($sched_id) {
        try {
             $this->checkStaffAccess();
            if (empty($sched_id) || !is_numeric($sched_id)) {
                return $this->response("error", [], "Valid schedule ID is required.");
            }

            $query = "DELETE FROM {$this->table} WHERE sched_id = :sched_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sched_id', $sched_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->response("success", [], "Schedule deleted successfully.");
            } else {
                return $this->response("info", [], "No schedule found with that ID.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Supplementary method
    public function getScheduleById($id) {
    try {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE sched_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->response('success', $result) : $this->response('error', [], 'Schedule not found');
    } catch (PDOException $e) {
        return $this->response('error', [], $e->getMessage());
    }
}

// Supplementary method
public function getAllDoctors() {
    try {
        $query = "SELECT doc_id, doc_first_name, doc_last_name FROM doctor ORDER BY doc_last_name, doc_first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'status' => 'success',
            'data' => $doctors,
            'message' => 'Doctors retrieved successfully.'
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