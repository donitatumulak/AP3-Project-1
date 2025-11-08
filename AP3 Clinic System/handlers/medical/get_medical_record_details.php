<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/medical/MedicalRecord.php';
require_once '../../classes/appointments/Appointment.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $record_id = $_GET['id'];

    if (!is_numeric($record_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid medical record ID.']);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->connect();
        $medicalRecord = new MedicalRecord($db);
        $appointment = new Appointment($db);
        
        $result = $medicalRecord->getMedicalRecordById($record_id);
        
        if ($result['status'] === 'success' && !empty($result['data'])) {
            $record = $result['data'];
            
            // Now appt_date should be available from the query
            $formattedApptId = $appointment->formatAppointmentId($record['appt_id'], $record['appt_date']);
            $result['data']['formatted_appt_id'] = $formattedApptId;
        }
        
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or missing ID.']);
}
?>