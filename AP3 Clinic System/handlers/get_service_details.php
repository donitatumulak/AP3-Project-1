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
            
            // Get service details
            $result = $service->getAllServices();
            
            if ($result['status'] === 'success') {
                // Find the specific service
                $service_data = null;
                foreach ($result['data'] as $serv) {
                    if ($serv['serv_id'] == $service_id) {
                        $service_data = $serv;
                        break;
                    }
                }
                
                if ($service_data) {
                    echo json_encode(['status' => 'success', 'data' => $service_data]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Service not found']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Could not load services']);
            }
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