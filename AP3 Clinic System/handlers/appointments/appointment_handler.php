<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/appointments/Appointment.php';
require_once '../classes/users/Patient.php';
require_once '../classes/users/Doctor.php';
require_once '../classes/appointments/Service.php';
require_once '../classes/appointments/Status.php';

$database = new Database();
$db = $database->connect();

$appointment = new Appointment($db);
$patient = new Patient($db);
$doctor = new Doctor($db);
$service = new Service($db);
$status = new Status($db);

if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];
    $appointmentData = $appointment->getAppointmentById($appointmentId);
    
    if ($appointmentData['status'] === 'success') {
        $appt = $appointmentData['data'];
        $patients = $patient->getAllPatient();
        $doctors = $doctor->getAllDoctors();
        $services = $service->getAllServices();
        $statuses = $status->getAllStatuses();
        ?>
        <form method="POST" action="/AP3%20Clinic%20System/pages/appointment_management.php">
            <input type="hidden" name="action" value="update_appointment">
            <input type="hidden" name="appt_id" value="<?php echo $appt['appt_id']; ?>">
            
            <div class="modal-body">
                <div class="row">
                    <!-- Patient Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="pat_id" class="form-label">Patient *</label>
                        <select class="form-select" id="pat_id" name="pat_id" required>
                            <option value="">Select Patient</option>
                            <?php if ($patients['status'] === 'success'): ?>
                                <?php foreach ($patients['data'] as $pat): ?>
                                    <option value="<?php echo $pat['pat_id']; ?>" 
                                        <?php echo $pat['pat_id'] == $appt['pat_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pat['pat_first_name'] . ' ' . $pat['pat_last_name']); ?> 
                                        (PAT-<?php echo $pat['pat_id']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Doctor Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="doc_id" class="form-label">Doctor *</label>
                        <select class="form-select" id="doc_id" name="doc_id" required>
                            <option value="">Select Doctor</option>
                            <?php if ($doctors['status'] === 'success'): ?>
                                <?php foreach ($doctors['data'] as $doc): ?>
                                    <option value="<?php echo $doc['doc_id']; ?>" 
                                        <?php echo $doc['doc_id'] == $appt['doc_id'] ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($doc['doc_first_name'] . ' ' . $doc['doc_last_name']); ?>
                                        (DOC-<?php echo $doc['doc_id']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Service Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="serv_id" class="form-label">Service *</label>
                        <select class="form-select" id="serv_id" name="serv_id" required>
                            <option value="">Select Service</option>
                            <?php if ($services['status'] === 'success'): ?>
                                <?php foreach ($services['data'] as $serv): ?>
                                    <option value="<?php echo $serv['serv_id']; ?>" 
                                        <?php echo $serv['serv_id'] == $appt['serv_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($serv['serv_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Status Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="stat_id" class="form-label">Status *</label>
                        <select class="form-select" id="stat_id" name="stat_id" required>
                            <option value="">Select Status</option>
                            <?php if ($statuses['status'] === 'success'): ?>
                                <?php foreach ($statuses['data'] as $stat): ?>
                                    <option value="<?php echo $stat['stat_id']; ?>" 
                                        <?php echo $stat['stat_id'] == $appt['stat_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($stat['stat_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6 mb-3">
                        <label for="appt_date" class="form-label">Appointment Date *</label>
                        <input type="date" class="form-control" id="appt_date" name="appt_date" 
                               value="<?php echo htmlspecialchars($appt['appt_date']); ?>" required>
                    </div>

                    <!-- Time -->
                    <div class="col-md-6 mb-3">
                        <label for="appt_time" class="form-label">Appointment Time *</label>
                        <input type="time" class="form-control" id="appt_time" name="appt_time" 
                               value="<?php echo htmlspecialchars($appt['appt_time']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">Update Appointment</button>
            </div>
        </form>
        <?php
    } else {
        echo '<div class="alert alert-danger text-center">Error: ' . htmlspecialchars($appointmentData['message']) . '</div>';
    }
} else {
    echo '<div class="alert alert-danger text-center">No appointment ID provided.</div>';
}
?>