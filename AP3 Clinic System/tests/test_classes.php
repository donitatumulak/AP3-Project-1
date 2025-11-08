<?php
// ✅ Include your database connection and class
include_once '../config/Database.php';
//include_once '../classes/users/Doctor.php'; //class 1 *
include_once '../classes/users/User.php'; //class 2 
//include_once '../classes/users/Patient.php'; //class 3 *
//include_once '../classes/users/Staff.php'; //class 4 *
//include_once '../classes/appointments/Appointment.php'; //class 5
//include_once '../classes/appointments/Schedule.php'; //class 6
//include_once '../classes/appointments/Service.php';  //class 7 *
//include_once '../classes/appointments/Status.php'; //class 8 *
//include_once '../classes/doctor/Specialization.php'; //class 9 *
//include_once '../classes/medical/MedicalRecord.php';  //class 10 *
//include_once '../classes/payments/Payment.php'; //class 11 *
//include_once '../classes/payments/PaymentStatus.php'; //class 12 *
//include_once '../classes/payments/PaymentMethod.php'; //class 13 *

// ✅ Initialize Database connection
$database = new Database();
$db = $database->connect();

// ✅ Initialize Specialization class
$user = new User($db);
//$doc = new Doctor($db);
//$pat = new Patient($db);
//$staff = new Staff($db);
//$appt = new Appointment($db);
//$sched = new Schedule($db);
//$serv = new Service($db);
//$stat = new Status($db);
//$spec = new Specialization($db);
//$med = new MedicalRecord($db);
//$pymt = new Payment($db);
//$pymeth = new PaymentMethod($db);
//$pystat = new PaymentStatus($db);
// -------------------------------
// TESTING SECTION
// Uncomment one block at a time
// -------------------------------

// 1️⃣ ADD (C)
// $result = $staff->addStaff('Jax', 'Yu', 'A', '091715986567', 'jaxyu@email.com');
// $result = $user->getUserWithProfile(56);
// 2️⃣ VIEW ALL (R)
// $result = $staff->getAllStaff();

// 3️⃣ VIEW  BY ID/NAME (R) 
// $result = $staff->getStaffById(1015);

// 4️⃣ UPDATE (U)
// $result = $staff->updateStaff(1015, 'Jaxyy', 'Yu', 'A', '091715986567', 'jaxyu@email.com');

// 5️⃣ DELETE (D)
//$result = $staff->deleteStaff(1015);

// 6️⃣ SPECIFIC QUERIES
// $result = $doc->getPreviousAppointments(1018);
// $result = $doc->getTodaysAppointments(1018);
// $result = $doc->getFutureAppointments(1018);


// ✅ Display output neatly
echo "<pre>";
print_r($result);
echo "</pre>";
?>
