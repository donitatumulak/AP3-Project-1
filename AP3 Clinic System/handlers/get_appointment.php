<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/appointments/Appointment.php'; // Fixed path

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['appt_id'])) {
    $appt_id = $_GET['appt_id'];
    
    if ($appt_id && is_numeric($appt_id)) {
        try {
            $database = new Database();
            $db = $database->connect(); // Use connect() instead of getConnection()
            $appointment = new Appointment($db);
            
            // Call your existing getAppointmentById method!
            $result = $appointment->getAppointmentById($appt_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid appointment ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>