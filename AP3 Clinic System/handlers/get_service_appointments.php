<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/appointments/Service.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $service_id = $_GET['id'];
    
    if ($service_id && is_numeric($service_id)) {
        try {
            $database = new Database();
            $db = $database->connect();
            $service = new Service($db);
            
            // Use the existing method from your Service class
            $result = $service->getAppointmentsByService($service_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid service ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>