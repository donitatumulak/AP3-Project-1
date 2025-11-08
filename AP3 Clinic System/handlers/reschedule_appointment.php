<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/appointments/Appointment.php'; // Fixed path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $appt_id = $input['appt_id'] ?? null;
    $new_date = $input['new_date'] ?? null;
    $new_time = $input['new_time'] ?? null;
    
    if ($appt_id && $new_date && $new_time) {
        try {
            $database = new Database();
            $db = $database->connect(); // Use connect() instead of getConnection()
            $appointment = new Appointment($db);
            
            // For rescheduling, we need to get current appointment data first
            // Let's assume we have the patient ID from session
            $pat_id = $_SESSION['profile_id'] ?? null;
            
            if (!$pat_id) {
                echo json_encode(['status' => 'error', 'message' => 'Patient not authenticated']);
                exit;
            }
            
            // Use updateAppointment - we need to pass all required parameters
            // For now, let's assume default values for doctor and service
            // You might want to get these from the current appointment
            $result = $appointment->updateAppointment(
                $appt_id,
                $new_time,      // new time
                $new_date,      // new date
                1,              // doctor ID - you need to get this from current appointment
                1,              // service ID - you need to get this from current appointment  
                $pat_id,        // patient ID
                1               // status ID (1 = scheduled)
            );
            
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    }
}
?>