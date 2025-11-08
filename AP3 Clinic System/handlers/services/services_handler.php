<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/services/Service.php';
require_once '../../classes/services/Specialization.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    if (!is_numeric($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->connect();
        
        switch ($action) {
            case 'get_service_details':
                $service = new Service($db);
                $result = $service->getServicesWithAppointmentCounts($id);
                break;
                
            case 'get_service_appointments':
                $service = new Service($db);
                $result = $service->getAppointmentsByService($id);
                break;
                
            case 'get_specialization_details':
                $specialization = new Specialization($db);
                $result = $specialization->getSpecializationById($id);
                break;
                
            case 'get_specialization_doctors':
                $specialization = new Specialization($db);
                $result = $specialization->getDoctorsBySpecialization($id);
                break;
                
            default:
                $result = ['status' => 'error', 'message' => 'Invalid action'];
        }
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>