<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/appointments/Appointment.php';

$database = new Database();
$db = $database->connect();
$appointment = new Appointment($db);

$action = $_GET['action'] ?? '';

if ($action === 'search_doctor') {
    $doctorName = $_GET['name'] ?? '';
    $result = $appointment->searchDoctorAppointments($doctorName);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>