<?php
session_start();
header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../classes/appointments/Schedule.php';

// ✅ Ensure only logged-in doctors can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Doctors only.']);
    exit();
}

$database = new Database();
$db = $database->connect();
$schedule = new Schedule($db);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? null;

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified.']);
    exit();
}

try {
    switch ($action) {
        // ➕ ADD NEW SCHEDULE
        case 'add':
            $doc_id = $_SESSION['profile_id'];
            $days   = $input['sched_days'] ?? '';
            $start  = $input['sched_start_time'] ?? '';
            $end    = $input['sched_end_time'] ?? '';

            $result = $schedule->addSchedule($doc_id, $days, $start, $end);
            echo json_encode($result);
            break;

        // ✏️ UPDATE SCHEDULE
        case 'update':
            $sched_id = $input['sched_id'] ?? null;
            $doc_id   = $_SESSION['profile_id'];
            $days     = $input['sched_days'] ?? '';
            $start    = $input['sched_start_time'] ?? '';
            $end      = $input['sched_end_time'] ?? '';

            $result = $schedule->updateSchedule($sched_id, $doc_id, $days, $start, $end);
            echo json_encode($result);
            break;

        // ❌ DELETE SCHEDULE
        case 'delete':
            $sched_id = $input['sched_id'] ?? null;
            if (!$sched_id) {
                echo json_encode(['status' => 'error', 'message' => 'Schedule ID missing.']);
                exit();
            }

            $result = $schedule->deleteSchedule($sched_id);
            echo json_encode($result);
            break;

        // 📄 GET SINGLE SCHEDULE
        case 'get':
            $sched_id = $_GET['sched_id'] ?? $input['sched_id'] ?? null;
            if (!$sched_id) {
                echo json_encode(['status' => 'error', 'message' => 'Schedule ID required.']);
                exit();
            }

            $result = $schedule->getScheduleById($sched_id);
            echo json_encode($result);
            break;

        // 📋 GET ALL SCHEDULES FOR LOGGED-IN DOCTOR
        case 'get_all':
            $doc_id = $_SESSION['profile_id'];
            $stmt = $db->prepare("
                SELECT s.sched_id, s.sched_days, s.sched_start_time, s.sched_end_time
                FROM schedule s
                WHERE s.doc_id = :doc_id
                ORDER BY FIELD(s.sched_days,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
                         s.sched_start_time ASC
            ");
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'message' => $result ? 'Schedules retrieved successfully.' : 'No schedules found.'
            ]);
            break;

        // 📅 GET TODAY'S SCHEDULES FOR LOGGED-IN DOCTOR
        case 'get_today':
            $doc_id = $_SESSION['profile_id'];
            $today = date('l');
            $stmt = $db->prepare("
                SELECT s.sched_id, s.sched_days, s.sched_start_time, s.sched_end_time
                FROM schedule s
                WHERE s.doc_id = :doc_id AND s.sched_days = :today
                ORDER BY s.sched_start_time ASC
            ");
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'message' => $result ? "Today's schedules retrieved successfully." : "No schedules for today."
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
