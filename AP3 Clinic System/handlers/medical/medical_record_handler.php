<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/Database.php';
require_once '../../classes/medical/MedicalRecord.php'; // adjust if different
// you may also need Appointment class for ownership checks
require_once '../../classes/appointments/Appointment.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$db = (new Database())->connect();
$medrec = new MedicalRecord($db);
$appt = new Appointment($db);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? null;

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action required']);
    exit();
}

$doctor_id = $_SESSION['profile_id'];

try {
    switch ($action) {

        // add: requires appt_id, med_rec_diagnosis, med_rec_prescription, med_rec_visit_date
        case 'add':
            $appt_id = $input['appt_id'] ?? null;
            $diagnosis = $input['med_rec_diagnosis'] ?? '';
            $prescription = $input['med_rec_prescription'] ?? '';
            $visit_date = $input['med_rec_visit_date'] ?? date('Y-m-d');

            // ownership check: appointment must belong to logged-in doctor
            $a = $appt->getAppointmentById($appt_id);
            if ($a['status'] !== 'success' || empty($a['data'])) {
                echo json_encode(['status'=>'error','message'=>'Appointment not found']);
                exit();
            }
            if ((int)$a['data']['doc_id'] !== (int)$doctor_id) {
                echo json_encode(['status'=>'error','message'=>'You cannot add a record to this appointment']);
                exit();
            }

            $res = $medrec->addMedicalRecord($appt_id, $diagnosis, $prescription, $visit_date);
            echo json_encode($res);
            break;

        // update: requires med_rec_id, appt_id, med_rec_diagnosis, med_rec_prescription, med_rec_visit_date
        case 'update':
            $med_rec_id = $input['med_rec_id'] ?? null;
            $appt_id = $input['appt_id'] ?? null;
            $diagnosis = $input['med_rec_diagnosis'] ?? '';
            $prescription = $input['med_rec_prescription'] ?? '';
            $visit_date = $input['med_rec_visit_date'] ?? date('Y-m-d');

            // check record exists
            $existing = $medrec->getMedicalRecordByAppointmentId($appt_id);
            // existing retrieval by appointment might return empty; instead, do ownership check via appointment
            $a = $appt->getAppointmentById($appt_id);
            if ($a['status'] !== 'success' || empty($a['data'])) {
                echo json_encode(['status'=>'error','message'=>'Appointment not found']);
                exit();
            }
            if ((int)$a['data']['doc_id'] !== (int)$doctor_id) {
                echo json_encode(['status'=>'error','message'=>'You cannot update a record for this appointment']);
                exit();
            }

            $res = $medrec->updateMedicalRecord($med_rec_id, $appt_id, $diagnosis, $prescription, $visit_date);
            echo json_encode($res);
            break;

        // get: get med rec by med_rec_id
        case 'get':
            $med_rec_id = $_GET['med_rec_id'] ?? $input['med_rec_id'] ?? null;
            if (!$med_rec_id) {
                echo json_encode(['status'=>'error','message'=>'med_rec_id required']);
                exit();
            }
            // ensure ownership: join to appointment and confirm doc_id
            $stmt = $db->prepare("SELECT mr.*, a.appt_date, p.pat_first_name, p.pat_last_name, a.pat_id, a.doc_id FROM medical_record mr INNER JOIN appointment a ON mr.appt_id = a.appt_id INNER JOIN patient p ON a.pat_id = p.pat_id WHERE mr.med_rec_id = :id");
            $stmt->bindParam(':id', $med_rec_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo json_encode(['status'=>'error','message'=>'Record not found']);
                exit();
            }
            if ((int)$row['doc_id'] !== (int)$doctor_id) {
                echo json_encode(['status'=>'error','message'=>'You cannot view this record']);
                exit();
            }
            $row['patient_name'] = trim($row['pat_first_name'] . ' ' . $row['pat_last_name']);
            echo json_encode(['status'=>'success','data'=>$row,'message'=>'Record found']);
            break;

        // get_all: return all medical records for logged-in doctor
        case 'get_all':
            $stmt = $db->prepare("SELECT mr.med_rec_id, mr.appt_id, mr.med_rec_diagnosis, mr.med_rec_prescription, mr.med_rec_visit_date, a.appt_date, p.pat_first_name, p.pat_last_name FROM medical_record mr INNER JOIN appointment a ON mr.appt_id = a.appt_id INNER JOIN patient p ON a.pat_id = p.pat_id WHERE a.doc_id = :doc_id ORDER BY mr.med_rec_id DESC");
            $stmt->bindParam(':doc_id', $doctor_id, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // format patient name
            foreach ($rows as &$r) {
                $r['patient_name'] = trim($r['pat_first_name'] . ' ' . $r['pat_last_name']);
            }
            echo json_encode(['status'=>'success','data'=>$rows,'message'=>$rows ? 'Records retrieved' : 'No records found']);
            break;

        // get_appts: return appointments for this doctor (to attach a medical record)
        case 'get_appts':
            $stmt = $db->prepare("SELECT a.appt_id, a.appt_date, p.pat_first_name, p.pat_last_name FROM appointment a INNER JOIN patient p ON a.pat_id = p.pat_id WHERE a.doc_id = :doc_id ORDER BY a.appt_date DESC");
            $stmt->bindParam(':doc_id', $doctor_id, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['patient_name'] = trim($r['pat_first_name'] . ' ' . $r['pat_last_name']);
            }
            echo json_encode(['status'=>'success','data'=>$rows,'message'=>$rows ? 'Appointments retrieved' : 'No appointments found']);
            break;
        
        case 'search_by_name':
            $name = $_GET['name'] ?? '';
            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Search term required']);
                exit();
            }
            echo json_encode($medrec->searchMedicalRecordsByPatientName($name, $doctor_id));
            break;

        default:
            echo json_encode(['status'=>'error','message'=>'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
