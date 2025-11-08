<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/payments/Payment.php';
require_once __DIR__ . '/../classes/payments/PaymentMethod.php';
require_once __DIR__ . '/../classes/payments/PaymentStatus.php';
require_once __DIR__ . '/../classes/appointments/Appointment.php';

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

        $payment = new Payment($db);
        $appointment = new Appointment($db);
        
        switch ($action) {
            case 'get_payment_details':
                // Get payment data via model
                //$result = $payment->getPaymentDetailsWithRelations($id);

                // Add formatted appointment ID if exists
                if ($result['status'] === 'success' && !empty($result['data']['appt_id'])) {
                    $result['data']['formatted_appt_id'] = $appointment->formatAppointmentId(
                        $result['data']['appt_id'],
                        $result['data']['appt_date'] ?? date('Y-m-d')
                    );
                }
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
