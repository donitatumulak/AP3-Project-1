<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/appointments/Appointment.php'; // Fixed path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $appt_id = $input['appt_id'] ?? null;
    
    if ($appt_id) {
        try {
            $database = new Database();
            $db = $database->connect(); // Use connect() instead of getConnection()
            $appointment = new Appointment($db);
            
            // Call your existing cancelAppointment method!
            $result = $appointment->cancelAppointment($appt_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid appointment ID']);
    }
}
?>