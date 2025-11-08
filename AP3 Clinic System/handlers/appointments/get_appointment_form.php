<?php
require_once '../../config/Database.php';
require_once '../../classes/appointments/Appointment.php';
require_once '../../classes/appointments/Schedule.php';
require_once '../../classes/appointments/Status.php';

$database = new Database();
$db = $database->connect();

$appointment = new Appointment($db);
$schedule = new Schedule($db);
$status = new Status($db);

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? null;

/* --------------------------
   ✏️ EDIT APPOINTMENT FORM (WITH PROPER DROPDOWNS)
-------------------------- */
if ($type === 'appointment_form') {
    $record = $appointment->getAppointmentById($id);
    
    // Get dropdown options
    $patients = $appointment->getAllPatients();
    $doctors = $appointment->getAllDoctors();
    $services = $appointment->getAllServices();
    $statuses = $appointment->getAllStatuses();
    
    if ($record['status'] === 'success' && !empty($record['data'])) {
        $a = $record['data'];
        
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/appointment_management.php">
            <input type="hidden" name="action" value="update_appointment">
            <input type="hidden" name="appt_id" value="' . htmlspecialchars($a['appt_id']) . '">
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="appt_date" class="form-control" value="' . htmlspecialchars($a['appt_date']) . '" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Time *</label>
                        <input type="time" name="appt_time" class="form-control" value="' . htmlspecialchars($a['appt_time']) . '" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Patient *</label>
                        <select class="form-select" name="pat_id" required>';
        
        // Patients dropdown
        if ($patients['status'] === 'success') {
            foreach ($patients['data'] as $patient) {
                $selected = ($patient['pat_id'] == $a['pat_id']) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($patient['pat_id']) . '" ' . $selected . '>'
                     . htmlspecialchars($patient['pat_first_name'] . ' ' . $patient['pat_last_name'])
                     . '</option>';
            }
        } else {
            echo '<option value="">No patients available</option>';
        }
        
        echo '</select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Doctor *</label>
                        <select class="form-select" name="doc_id" required>';
        
        // Doctors dropdown
        if ($doctors['status'] === 'success') {
            foreach ($doctors['data'] as $doctor) {
                $selected = ($doctor['doc_id'] == $a['doc_id']) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($doctor['doc_id']) . '" ' . $selected . '>'
                     . htmlspecialchars('Dr. ' . $doctor['doc_first_name'] . ' ' . $doctor['doc_last_name'])
                     . '</option>';
            }
        } else {
            echo '<option value="">No doctors available</option>';
        }
        
        echo '</select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Service *</label>
                        <select class="form-select" name="serv_id" required>';
        
        // Services dropdown
        if ($services['status'] === 'success') {
            foreach ($services['data'] as $service) {
                $selected = ($service['serv_id'] == $a['serv_id']) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($service['serv_id']) . '" ' . $selected . '>'
                     . htmlspecialchars($service['serv_name'])
                     . '</option>';
            }
        } else {
            echo '<option value="">No services available</option>';
        }
        
        echo '</select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="stat_id" required>';
        
        // Statuses dropdown
        if ($statuses['status'] === 'success') {
            foreach ($statuses['data'] as $statusItem) {
                $selected = ($statusItem['stat_id'] == $a['stat_id']) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($statusItem['stat_id']) . '" ' . $selected . '>'
                     . htmlspecialchars($statusItem['stat_name'])
                     . '</option>';
            }
        } else {
            echo '<option value="">No statuses available</option>';
        }
        
        echo '</select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">Update Appointment</button>
            </div>
        </form>';
    } else {
        echo '<div class="p-3 text-center text-danger">Unable to load appointment. Error: ' . htmlspecialchars($record['message'] ?? 'Unknown error') . '</div>';
    }
}

/* --------------------------
   🔄 STATUS UPDATE FORM
-------------------------- */
elseif ($type === 'status_update_form') {
    $record = $appointment->getAppointmentById($id);
    if ($record['status'] === 'success') {
        $a = $record['data'];
        $allStatuses = $status->getAllStatuses();
        
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/appointment_management.php">
            <input type="hidden" name="action" value="update_appointment_status">
            <input type="hidden" name="appt_id" value="' . $a['appt_id'] . '">
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Appointment</label>
                    <input type="text" class="form-control" value="' . htmlspecialchars($a['formatted_id'] ?? 'Appointment ' . $a['appt_id']) . '" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Status</label>
                    <input type="text" class="form-control" value="' . htmlspecialchars($a['status_name']) . '" readonly>
                </div>
                <div class="mb-3">
                    <label for="stat_id" class="form-label">New Status</label>
                    <select class="form-select" id="stat_id" name="stat_id" required>';
        
        if ($allStatuses['status'] === 'success') {
            foreach ($allStatuses['data'] as $stat) {
                $statId = $stat['stat_id'] ?? $stat['id'] ?? '';
                $statName = $stat['stat_name'] ?? $stat['name'] ?? '';
                $selected = ($statId == $a['stat_id']) ? 'selected' : '';
                echo '<option value="' . $statId . '" ' . $selected . '>' . htmlspecialchars($statName) . '</option>';
            }
        } else {
            echo '<option value="">No statuses available</option>';
        }
        
        echo '</select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">Update Status</button>
            </div>
        </form>';
    } else {
        echo '<div class="p-3 text-center text-danger">Unable to load appointment. Error: ' . ($record['message'] ?? 'Unknown error') . '</div>';
    }
}

/* --------------------------
   📅 SCHEDULE FORM (WITH CHECKBOXES FOR DAYS)
-------------------------- */
elseif ($type === 'schedule_form') {
    $record = $schedule->getScheduleById($id);
    $doctors_data = $schedule->getAllDoctors();
    
    if ($record['status'] === 'success') {
        $s = $record['data'];
        $currentDays = $s['sched_days'] ?? $s['days'] ?? '';
        
        // Convert current days string to array for checking
        $currentDaysArray = array_map('trim', explode(',', $currentDays));
        
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/appointment_management.php">
            <input type="hidden" name="action" value="update_schedule">
            <input type="hidden" name="sched_id" value="' . ($s['sched_id'] ?? $s['id'] ?? '') . '">
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Doctor *</label>
                    <select class="form-select" name="doc_id" required>';
        
        // Doctors dropdown
        if ($doctors_data['status'] === 'success') {
            foreach ($doctors_data['data'] as $doctor) {
                $selected = ($doctor['doc_id'] == ($s['doc_id'] ?? $s['doctor_id'] ?? '')) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($doctor['doc_id']) . '" ' . $selected . '>'
                     . htmlspecialchars('Dr. ' . $doctor['doc_first_name'] . ' ' . $doctor['doc_last_name'])
                     . '</option>';
            }
        }
        
        echo '</select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Days *</label>
                    <div class="days-checkbox-group border rounded p-3 bg-light">
                        <div class="row">';
        
        $days = [
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday'
        ];
        
        foreach ($days as $value => $label) {
            $checked = in_array($value, $currentDaysArray) ? 'checked' : '';
            echo '<div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" value="' . $value . '" id="day_' . $value . '" ' . $checked . '>
                        <label class="form-check-label" for="day_' . $value . '">' . $label . '</label>
                    </div>
                  </div>';
        }
        
        echo '</div>
                    </div>
                    <input type="hidden" name="sched_days" id="selected_days" value="' . htmlspecialchars($currentDays) . '" required>
                    <small class="text-muted">Selected: <span id="selected_days_display">' . htmlspecialchars($currentDays) . '</span></small>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Time *</label>
                        <input type="time" name="sched_start_time" class="form-control" value="' . htmlspecialchars($s['sched_start_time'] ?? $s['start_time'] ?? '') . '" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Time *</label>
                        <input type="time" name="sched_end_time" class="form-control" value="' . htmlspecialchars($s['sched_end_time'] ?? $s['end_time'] ?? '') . '" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">Update Schedule</button>
            </div>
        </form>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkboxes = document.querySelectorAll(".day-checkbox");
            const hiddenInput = document.getElementById("selected_days");
            const displaySpan = document.getElementById("selected_days_display");
            
            function updateSelectedDays() {
                const selectedDays = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);
                
                const daysString = selectedDays.join(", ");
                hiddenInput.value = daysString;
                displaySpan.textContent = daysString || "None selected";
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener("change", updateSelectedDays);
            });
            
            // Initial update
            updateSelectedDays();
        });
        </script>';
    }
}

/* --------------------------
   🏷️ STATUS FORM
-------------------------- */
elseif ($type === 'status_form') {
    $record = $status->getStatusById($id);
    if ($record['status'] === 'success') {
        $s = $record['data'];
        echo '<form method="POST" action="/AP3%20Clinic%20System/pages/appointment_management.php">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="stat_id" value="' . ($s['stat_id'] ?? $s['id'] ?? '') . '">
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Status Name</label>
                    <input type="text" name="stat_name" class="form-control" value="' . htmlspecialchars($s['stat_name'] ?? $s['name'] ?? '') . '" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal">Update Status</button>
            </div>
        </form>';
    } else {
        echo '<div class="p-3 text-center text-danger">Unable to load status. Error: ' . ($record['message'] ?? 'Unknown error') . '</div>';
    }
}

/* --------------------------
   📝 APPOINTMENT DETAILS FORM
-------------------------- */
elseif ($type === 'appointment_details') {
    $record = $appointment->getAppointmentByDocId($id);
    if ($record['status'] === 'success' && !empty($record['data'])) {
        $a = $record['data'];

        echo '<div class="row">
                <div class="col-md-6 mb-3"><strong>Appointment ID:</strong> ' . htmlspecialchars($a['appt_id']) . '</div>
                <div class="col-md-6 mb-3"><strong>Date & Time:</strong> ' . htmlspecialchars(date('M j, Y g:i A', strtotime($a['appt_date'] . ' ' . $a['appt_time']))) . '</div>
                <div class="col-md-6 mb-3"><strong>Patient:</strong> ' . htmlspecialchars($a['pat_first_name'] . ' ' . $a['pat_last_name']) . '</div>
                <div class="col-md-6 mb-3"><strong>Doctor:</strong> Dr. ' . htmlspecialchars($a['doc_first_name'] . ' ' . $a['doc_last_name']) . '</div>
                <div class="col-md-6 mb-3"><strong>Service:</strong> ' . htmlspecialchars($a['serv_name']) . '</div>
                <div class="col-md-6 mb-3"><strong>Status:</strong> ' . htmlspecialchars($a['stat_name']) . '</div>
              </div>';
    } else {
        echo '<div class="text-center text-danger">Unable to load appointment details.</div>';
    }
}


// Handle invalid requests
else {
    echo '<div class="p-3 text-center text-danger">Invalid request type.</div>';
}
?>