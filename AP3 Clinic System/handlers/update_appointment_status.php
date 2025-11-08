<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/appointments/Appointment.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $appt_id = $input['appt_id'] ?? null;
    $stat_id = $input['stat_id'] ?? null;
    
    if ($appt_id && $stat_id) {
        try {
            $database = new Database();
            $db = $database->connect();
            $appointment = new Appointment($db);
            
            // Use the updateAppointmentStatus method from your Appointment class
            $result = $appointment->updateAppointmentStatus($appt_id, $stat_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing appointment ID or status ID']);
    }
}
?>