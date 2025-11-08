<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="services_management.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_service">
                    
                    <div class="mb-3">
                        <label for="serv_name" class="form-label">Service Name *</label>
                        <input type="text" class="form-control" id="serv_name" name="serv_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="serv_description" class="form-label">Description</label>
                        <textarea class="form-control" id="serv_description" name="serv_description" rows="3" placeholder="Optional service description..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="serv_price" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="serv_price" name="serv_price" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editServiceModalBody">
                <!-- Form will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add Specialization Modal -->
<div class="modal fade" id="addSpecializationModal" tabindex="-1" aria-labelledby="addSpecializationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSpecializationModalLabel">Add New Specialization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="services_management.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_specialization">
                    
                    <div class="mb-3">
                        <label for="spec_name" class="form-label">Specialization Name *</label>
                        <input type="text" class="form-control" id="spec_name" name="spec_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal">Add Specialization</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Specialization Modal -->
<div class="modal fade" id="editSpecializationModal" tabindex="-1" aria-labelledby="editSpecializationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSpecializationModalLabel">Edit Specialization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editSpecializationModalBody">
                <!-- Form will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>