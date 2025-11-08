<?php
session_start();
require_once '../../config/Database.php';
require_once '../../classes/services/Service.php';
require_once '../../classes/services/Specialization.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    if (!is_numeric($id)) {
        echo '<div class="p-3 text-danger text-center">Invalid ID.</div>';
        exit;
    }

    try {
        $database = new Database();
        $db = $database->connect();
        
        switch ($action) {
            case 'get_service_form':
                $service = new Service($db);
                $result = $service->getServiceById($id);
                
                if ($result['status'] === 'success' && !empty($result['data'])) {
                    $service_data = $result['data'];
                    ?>
                    <form method="POST" action="services_management.php">
                        <input type="hidden" name="action" value="update_service">
                        <input type="hidden" name="serv_id" value="<?= $service_data['serv_id'] ?>">
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Service Name *</label>
                                <input type="text" class="form-control" name="serv_name" 
                                    value="<?= htmlspecialchars($service_data['serv_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" name="serv_price" 
                                    value="<?= $service_data['serv_price'] ?>" step="0.01" placeholder="0.00">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="serv_description" rows="3" 
                                    placeholder="Enter service description..."><?= htmlspecialchars($service_data['serv_description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-teal">Save Changes</button>
                        </div>
                    </form>
                    <?php
                } else {
                    echo '<div class="p-3 text-center text-danger">Service not found.</div>';
                }
                break;
                
            case 'get_specialization_form':
                $specialization = new Specialization($db);
                $result = $specialization->getSpecializationById($id);
                
                if ($result['status'] === 'success' && !empty($result['data'])) {
                    $spec_data = $result['data'];
                    ?>
                    <form method="POST" action="services_management.php">
                        <input type="hidden" name="action" value="update_specialization">
                        <input type="hidden" name="spec_id" value="<?= $spec_data['spec_id'] ?>">
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Specialization Name *</label>
                                <input type="text" class="form-control" name="spec_name" 
                                    value="<?= htmlspecialchars($spec_data['spec_name']) ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-teal">Save Changes</button>
                        </div>
                    </form>
                    <?php
                } else {
                    echo '<div class="p-3 text-center text-danger">Specialization not found.</div>';
                }
                break;
                
            default:
                echo '<div class="p-3 text-center text-danger">Invalid action.</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="p-3 text-center text-danger">Server error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="p-3 text-center text-danger">Invalid request.</div>';
}
?>