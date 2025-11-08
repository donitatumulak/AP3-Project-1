<?php
/**
 * APPOINTMENT MANAGEMENT MODALS
 * Specific modals for appointment management functionality
 */
?>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="addAppointmentModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i>Add New Appointment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="appointment_management.php">
                <input type="hidden" name="action" value="add_appointment">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="appt_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time *</label>
                            <input type="time" name="appt_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient *</label>
                            <select class="form-select" name="pat_id" required>
                                <option value="">Select Patient</option>
                                <?php if ($patients_data['status'] === 'success'): ?>
                                    <?php foreach ($patients_data['data'] as $patient): ?>
                                        <option value="<?php echo $patient['pat_id']; ?>">
                                            <?php echo htmlspecialchars($patient['pat_first_name'] . ' ' . $patient['pat_last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Doctor *</label>
                            <select class="form-select" name="doc_id" required>
                                <option value="">Select Doctor</option>
                                <?php if ($doctors_data['status'] === 'success'): ?>
                                    <?php foreach ($doctors_data['data'] as $doctor): ?>
                                        <option value="<?php echo $doctor['doc_id']; ?>">
                                            Dr. <?php echo htmlspecialchars($doctor['doc_first_name'] . ' ' . $doctor['doc_last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Service *</label>
                            <select class="form-select" name="serv_id" required>
                                <option value="">Select Service</option>
                                <?php if ($services_data['status'] === 'success'): ?>
                                    <?php foreach ($services_data['data'] as $service): ?>
                                        <option value="<?php echo $service['serv_id']; ?>">
                                            <?php echo htmlspecialchars($service['serv_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="stat_id" required>
                                <option value="">Select Status</option>
                                <?php if ($statuses_data['status'] === 'success'): ?>
                                    <?php foreach ($statuses_data['data'] as $status): ?>
                                        <option value="<?php echo $status['stat_id']; ?>">
                                            <?php echo htmlspecialchars($status['stat_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="editAppointmentModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Appointment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editAppointmentModalBody">
                <!-- Form will be loaded via AJAX -->
                <div class="text-center p-4">
                    <div class="spinner-border text-teal" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="fas fa-sync me-2"></i>Update Appointment Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="updateStatusModalBody">
                <!-- Form will be loaded via AJAX -->
                <div class="text-center p-4">
                    <div class="spinner-border text-teal" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="addScheduleModalLabel">
                    <i class="fas fa-clock me-2"></i>Add New Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="appointment_management.php">
                <input type="hidden" name="action" value="add_schedule">
                
                <div class="modal-body">
                    <!-- Doctor Field -->
                    <div class="mb-3">
                        <label class="form-label">Doctor *</label>
                        <select class="form-select" name="doc_id" required>
                            <option value="">Select Doctor</option>
                            <?php if ($schedule_doctors_data['status'] === 'success'): ?>
                                <?php foreach ($schedule_doctors_data['data'] as $doctor): ?>
                                    <option value="<?php echo $doctor['doc_id']; ?>">
                                        Dr. <?php echo htmlspecialchars($doctor['doc_first_name'] . ' ' . $doctor['doc_last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Days Field - Vertical Layout -->
                    <div class="mb-3">
                        <label class="form-label">Days *</label>
                        <div class="days-checkbox-group border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Monday" id="add_day_monday">
                                        <label class="form-check-label" for="add_day_monday">Monday</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Tuesday" id="add_day_tuesday">
                                        <label class="form-check-label" for="add_day_tuesday">Tuesday</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Wednesday" id="add_day_wednesday">
                                        <label class="form-check-label" for="add_day_wednesday">Wednesday</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Thursday" id="add_day_thursday">
                                        <label class="form-check-label" for="add_day_thursday">Thursday</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Friday" id="add_day_friday">
                                        <label class="form-check-label" for="add_day_friday">Friday</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Saturday" id="add_day_saturday">
                                        <label class="form-check-label" for="add_day_saturday">Saturday</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input day-checkbox" type="checkbox" value="Sunday" id="add_day_sunday">
                                        <label class="form-check-label" for="add_day_sunday">Sunday</label>
                                    </div>  
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="sched_days" id="add_selected_days" value="" required>
                        <small class="text-muted">Selected: <span id="add_selected_days_display">None selected</span></small>
                    </div>

                    <!-- Time Fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="sched_start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="sched_end_time" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Schedule Days Checkboxes -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Function to handle days selection for schedule modal
    function initializeScheduleDays() {
        const addCheckboxes = document.querySelectorAll("#addScheduleModal .day-checkbox");
        const addHiddenInput = document.getElementById("add_selected_days");
        const addDisplaySpan = document.getElementById("add_selected_days_display");
        
        function updateSelectedDays() {
            const selectedDays = Array.from(addCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
            
            const daysString = selectedDays.join(", ");
            addHiddenInput.value = daysString;
            addDisplaySpan.textContent = daysString || "None selected";
            
            // Validate - at least one day must be selected
            if (selectedDays.length === 0) {
                addHiddenInput.setCustomValidity("Please select at least one day");
            } else {
                addHiddenInput.setCustomValidity("");
            }
        }
        
        addCheckboxes.forEach(checkbox => {
            checkbox.addEventListener("change", updateSelectedDays);
        });
        
        // Initial update
        updateSelectedDays();
    }
    
    // Quick select functions
    window.selectWeekdays = function() {
        const weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        document.querySelectorAll("#addScheduleModal .day-checkbox").forEach(checkbox => {
            checkbox.checked = weekdays.includes(checkbox.value);
        });
        initializeScheduleDays(); // Trigger update
    };
    
    window.selectWeekends = function() {
        const weekends = ['Saturday', 'Sunday'];
        document.querySelectorAll("#addScheduleModal .day-checkbox").forEach(checkbox => {
            checkbox.checked = weekends.includes(checkbox.value);
        });
        initializeScheduleDays(); // Trigger update
    };
    
    window.clearAllDays = function() {
        document.querySelectorAll("#addScheduleModal .day-checkbox").forEach(checkbox => {
            checkbox.checked = false;
        });
        initializeScheduleDays(); // Trigger update
    };
    
    // Initialize when modal is shown
    document.getElementById('addScheduleModal').addEventListener('show.bs.modal', function () {
        setTimeout(initializeScheduleDays, 100);
    });
    
    // Also initialize if modal is already open
    if (document.getElementById('addScheduleModal').classList.contains('show')) {
        initializeScheduleDays();
    }
    
    // Reset form when modal is hidden
    document.getElementById('addScheduleModal').addEventListener('hidden.bs.modal', function () {
        // Reset checkboxes
        document.querySelectorAll("#addScheduleModal .day-checkbox").forEach(checkbox => {
            checkbox.checked = false;
        });
        // Reset hidden field and display
        document.getElementById("add_selected_days").value = "";
        document.getElementById("add_selected_days_display").textContent = "None selected";
    });
});
</script>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="editScheduleModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editScheduleModalBody">
                <!-- Form will be loaded via AJAX -->
                <div class="text-center p-4">
                    <div class="spinner-border text-teal" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1" aria-labelledby="addStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="addStatusModalLabel">
                    <i class="fas fa-tag me-2"></i>Add New Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="appointment_management.php">
                <input type="hidden" name="action" value="add_status">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="stat_name" class="form-label">Status Name *</label>
                        <input type="text" class="form-control" id="stat_name" name="stat_name" 
                               placeholder="e.g., Confirmed, Rescheduled, No-show" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title" id="editStatusModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editStatusModalBody">
                <!-- Form will be loaded via AJAX -->
                <div class="text-center p-4">
                    <div class="spinner-border text-teal" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-teal text-white">
        <h5 class="modal-title">Appointment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="appointmentDetailsModalBody">
        <div class="text-center py-3">
          <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
          <p>Loading details...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
