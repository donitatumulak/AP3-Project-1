<?php
session_start();
$user_type = $_SESSION['user_type'] ?? '';
$is_staff = ($user_type === 'staff');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Allow access to: superadmin and staff (with restrictions)
$allowed_users = ['superadmin','staff'];
if (!in_array($user_type, $allowed_users)) {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. You don't have permission to view this page.</div>";
    require_once '../includes/footer.php';
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->connect();

require_once '../classes/services/Service.php';
require_once '../classes/services/Specialization.php';

// Initialize classes
$service = new Service($db);
$specialization = new Specialization($db);

// Handle form submissions (with staff restrictions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        // Service actions - staff can add/update but NOT delete
        case 'add_service':
            $result = $service->addService(
                $_POST['serv_name'],
                $_POST['serv_description'] ?? '',
                $_POST['serv_price'] ?? null
            );
            break;
            
        case 'update_service':
            $result = $service->updateService(
                $_POST['serv_id'],
                $_POST['serv_name'],
                $_POST['serv_description'] ?? '',
                $_POST['serv_price'] ?? null
            );
            break;
            
        case 'delete_service':
            if (!$is_staff) {
                $result = $service->deleteService($_POST['serv_id']);
            }
            break;
            
        // Specialization actions - staff can ONLY update, NOT add/delete
        case 'add_specialization':
            if (!$is_staff) {
                $result = $specialization->addSpecialization($_POST['spec_name']);
            }
            break;
            
        case 'update_specialization':
            $result = $specialization->updateSpecialization(
                $_POST['spec_id'],
                $_POST['spec_name']
            );
            break;
            
        case 'delete_specialization':
            if (!$is_staff) {
                $result = $specialization->deleteSpecialization($_POST['spec_id']);
            }
            break;
    }
    
    if (isset($result)) {
        $_SESSION['message'] = [
            'type' => $result['status'],
            'text' => $result['message']
        ];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Get all data for display
$services = $service->getAllServices();
$specializations = $specialization->getAllSpecializations();
$services_with_counts = $service->getServicesWithAppointmentCounts(100); // Get all services with counts

$page = 'pages/services_management';
include '../includes/header.php';
?>

<body class="management-page">
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar_user.php'; ?>
            
            <!-- Main Content -->
            <div class="col-lg-10 management-content">
                <!-- Welcome Header -->
                <div class="management-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1><i class="fas fa-concierge-bell me-3"></i>Services & Specializations Management</h1>
                            <p class="text-muted mb-0">
                                <?php echo $is_staff ? 'View clinic services and specializations' : 'Manage clinic services and doctor specializations'; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card">
                                <div class="stats-content">
                                    <i class="fas fa-stethoscope stats-icon"></i>
                                    <div class="stats-text">
                                        <div class="stats-number">
                                            <?php 
                                            $total = 0;
                                            if ($services['status'] === 'success') $total += count($services['data']);
                                            if ($specializations['status'] === 'success') $total += count($specializations['data']);
                                            echo $total;
                                            ?>
                                        </div>
                                        <div class="stats-label">Total Items</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message']['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                        <?php echo $_SESSION['message']['text']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- Services & Specializations Tabs -->
                <div class="management-tabs">
                    <nav>
                        <div class="nav nav-tabs" id="servicesTabs" role="tablist">
                            <button class="nav-link active" id="services-tab" data-bs-toggle="tab" 
                                    data-bs-target="#services" type="button" role="tab">
                                <i class="fas fa-concierge-bell"></i>
                                Services
                                <span class="badge bg-primary ms-1">
                                    <?php echo $services['status'] === 'success' ? count($services['data']) : 0; ?>
                                </span>
                            </button>

                            <button class="nav-link" id="specializations-tab" data-bs-toggle="tab" 
                                    data-bs-target="#specializations" type="button" role="tab">
                                <i class="fas fa-user-md"></i>
                                Specializations
                                <span class="badge bg-success ms-1">
                                    <?php echo $specializations['status'] === 'success' ? count($specializations['data']) : 0; ?>
                                </span>
                            </button>
                        </div>
                    </nav>

                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white">
                        <!-- Services Tab -->
                        <div class="tab-pane fade show active" id="services" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search services..." 
                                                   id="search-services">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <button class="btn btn-teal" onclick="openAddServiceModal()">
                                            <i class="fas fa-plus"></i> Add Service
                                        </button>
                                    </div>
                                </div>

                                <!-- Services Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="services-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th width="8%">ID</th>
                                                <th width="15%">Service Name</th>
                                                <th width="30%">Description</th>
                                                <th width="10%">Price</th>
                                                <th width="15%">Appointments</th>
                                                <th width="12%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($services_with_counts['status'] === 'success' && !empty($services_with_counts['data'])): ?>
                                                <?php foreach ($services_with_counts['data'] as $serv): ?>
                                                <tr>
                                                    <td><?php echo $serv['serv_id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($serv['serv_name']); ?></strong>
                                                        </td>
                                                        <td class="description-cell">
                                                        <?php if (!empty($serv['serv_description'])): ?>
                                                            <div class="service-description">
                                                                <?php echo htmlspecialchars($serv['serv_description']); ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">No description</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($serv['serv_price'])): ?>
                                                            <span class="badge pastel-green">₱<?php echo number_format($serv['serv_price'], 2); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge pastel-gray">Not set</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge pastel-blue"><?php echo $serv['total_appointments']; ?> appointments</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-outline-primary btn-action" 
                                                                    onclick="viewServiceAppointments(<?php echo $serv['serv_id']; ?>, '<?php echo htmlspecialchars($serv['serv_name']); ?>')"
                                                                    title="View Appointments">
                                                                <i class="fas fa-calendar-alt"></i>
                                                            </button>
                                                            <button class="btn btn-outline-warning btn-action" 
                                                                    onclick="openEditServiceModal(<?php echo $serv['serv_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if (!$is_staff): ?>
                                                                <button class="btn btn-outline-danger btn-action"
                                                                        onclick="confirmDelete(<?php echo $serv['serv_id']; ?>, '<?php echo htmlspecialchars($serv['serv_name']); ?>', 'service', 'services_management.php')"
                                                                        title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-concierge-bell fa-2x mb-2 d-block"></i>
                                                        <?php echo $services['status'] === 'error' ? 'Error: ' . htmlspecialchars($services['message']) : 'No services found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($services_with_counts['status'] === 'success' && !empty($services_with_counts['data'])):?>
                                <nav aria-label="Services pagination">
                                    <ul class="pagination justify-content-center mt-3" id="services-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Specializations Tab -->
                        <div class="tab-pane fade" id="specializations" role="tabpanel">
                            <div class="management-table-container">
                                <!-- Table Header with Search and Actions -->
                                <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <div class="search-section">
                                        <div class="input-group search-box">
                                            <input type="text" class="form-control" 
                                                   placeholder="Search specializations..." 
                                                   id="search-specializations">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-section">
                                        <?php if (!$is_staff): ?>
                                            <button class="btn btn-teal" onclick="openAddSpecializationModal()">
                                                <i class="fas fa-plus"></i> Add Specialization
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-eye me-1"></i>View & Edit Only
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Specializations Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="specializations-table">
                                        <thead class="table-teal">
                                            <tr>
                                                <th width="15%" class="text-center">Specialization ID</th>
                                                <th width="65%" class="text-center">Specialization Name</th>
                                                <th width="25%" class="text-start ps-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($specializations['status'] === 'success' && !empty($specializations['data'])): ?>
                                                <?php foreach ($specializations['data'] as $spec): ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <span><?php echo $spec['spec_id']; ?></span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <h6 class="mb-0 text-teal"><?php echo htmlspecialchars($spec['spec_name']); ?></h6>
                                                    </td>
                                                    <td class="align-middle"> 
                                                        <div class="d-flex gap-1">
                                                             <button class="btn btn-outline-primary btn-action" 
                                                                    onclick="viewSpecializationDoctors(<?php echo $spec['spec_id']; ?>, '<?php echo htmlspecialchars($spec['spec_name']); ?>')"
                                                                    title="View Doctors">
                                                                <i class="fas fa-user-md"></i>
                                                            </button>
                                                            <button class="btn btn-outline-warning btn-action" 
                                                                    onclick="openEditSpecializationModal(<?php echo $spec['spec_id']; ?>)"
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if (!$is_staff): ?>
                                                                <button class="btn btn-outline-danger btn-action"
                                                                        onclick="confirmDelete(<?php echo $spec['spec_id']; ?>, '<?php echo htmlspecialchars($spec['spec_name']); ?>', 'specialization', 'services_management.php')"
                                                                        title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">
                                                        <i class="fas fa-user-md fa-2x mb-2 d-block"></i>
                                                        <?php echo $specializations['status'] === 'error' ? 'Error: ' . htmlspecialchars($specializations['message']) : 'No specializations found.'; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($specializations['status'] === 'success' && !empty($specializations['data'])):?>
                                <nav aria-label="Specializations pagination">
                                    <ul class="pagination justify-content-center mt-3" id="specializations-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Reusable Modals -->
    <?php include '../components/management_modals.php'; ?>

    <!-- Services Specific Modals -->
    <?php
    // Add Service Modal (staff CAN use this)
    renderAddModal('addServiceModal', 'Service', 'services_management.php', 'service', [
        ['name' => 'serv_name', 'label' => 'Service Name', 'type' => 'text', 'required' => true, 'width' => 'col-md-6'],
        ['name' => 'serv_price', 'label' => 'Price', 'type' => 'number', 'required' => false, 'width' => 'col-md-6', 'placeholder' => '0.00'],
        ['name' => 'serv_description', 'label' => 'Description', 'type' => 'textarea', 'required' => false, 'rows' => 3, 'placeholder' => 'Enter service description...']
    ]);

    // Edit Service Modal (staff CAN use this)
    renderEditModal('editServiceModal', 'Service', 'service');

    // Add Specialization Modal (staff CANNOT use this)
    if (!$is_staff) {
        renderAddModal('addSpecializationModal', 'Specialization', 'services_management.php', 'specialization', [
            ['name' => 'spec_name', 'label' => 'Specialization Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter specialization name...']
        ]);
    }

    // Edit Specialization Modal (staff CAN use this)
    renderEditModal('editSpecializationModal', 'Specialization', 'specialization');
    ?>

    <!-- SweetAlert + JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/management.js"></script>

    <script>
    // Service Functions
    function openAddServiceModal() {
        const modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
        modal.show();
    }

    function openEditServiceModal(serviceId) {
        const modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
        fetch(`../handlers/services/services_form_handler.php?action=get_service_form&id=${serviceId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editServiceModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load service form', 'error');
            });
    }

    function viewServiceDetails(serviceId) {
        const customLabels = {
            'serv_id': 'Service ID',
            'serv_name': 'Service Name',
            'serv_description': 'Description',
            'serv_price': 'Price',
            'total_appointments': 'Total Appointments'
        };
        
        const allowedFields = ['serv_id', 'serv_name', 'serv_description', 'serv_price', 'total_appointments'];
        
        viewItemDetails(serviceId, 'service', '../handlers/services/services_handler.php?action=get_service_details', customLabels, allowedFields);
    }

    // Specialization Functions
    function openAddSpecializationModal() {
        <?php if (!$is_staff): ?>
            const modal = new bootstrap.Modal(document.getElementById('addSpecializationModal'));
            modal.show();
        <?php endif; ?>
    }

    function openEditSpecializationModal(specId) {
        const modal = new bootstrap.Modal(document.getElementById('editSpecializationModal'));
        fetch(`../handlers/services/services_form_handler.php?action=get_specialization_form&id=${specId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editSpecializationModalBody').innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error loading form:', error);
                Swal.fire('Error!', 'Failed to load specialization form', 'error');
            });
    }

    function viewSpecializationDetails(specId) {
        const customLabels = {
            'spec_id': 'Specialization ID',
            'spec_name': 'Specialization Name'
        };
        
        const allowedFields = ['spec_id', 'spec_name'];
        
        viewItemDetails(specId, 'specialization', '../handlers/services/services_handler.php?action=get_specialization_details', customLabels, allowedFields);
    }

    // View Specialization Doctors
    function viewSpecializationDoctors(specId, specName) {
        fetch(`../handlers/services/services_handler.php?action=get_specialization_doctors&id=${specId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const doctors = data.data;
                    let doctorsHtml = '<div class="text-start">';
                    
                    if (doctors.length > 0) {
                        doctorsHtml += `<p><strong>Doctors specialized in ${specName}:</strong></p>`;
                        doctors.forEach(doctor => {
                            doctorsHtml += `
                                <div class="border-bottom pb-2 mb-2">
                                    <p><strong>${doctor.doctor_name}</strong></p>
                                    ${doctor.doctor_email ? `<p><strong>Email:</strong> ${doctor.doctor_email}</p>` : ''}
                                    ${doctor.doctor_phone ? `<p><strong>Phone:</strong> ${doctor.doctor_phone}</p>` : ''}
                                </div>
                            `;
                        });
                    } else {
                        doctorsHtml += '<p class="text-muted">No doctors found for this specialization.</p>';
                    }
                    
                    doctorsHtml += '</div>';
                    
                    Swal.fire({
                        title: `Doctors - ${specName}`,
                        html: doctorsHtml,
                        width: 500,
                        showCloseButton: true,
                        showConfirmButton: false,
                        customClass: {
                        title: 'swal-teal-header'
                    }
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error loading doctors:', error);
                Swal.fire('Error!', 'Failed to load doctors', 'error');
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeSearch('search-services', 'services-table');
        initializeSearch('search-specializations', 'specializations-table');
        
        initializePagination('services-table', 'services-pagination', 10);
        initializePagination('specializations-table', 'specializations-pagination', 10);
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function viewServiceAppointments(serviceId, serviceName) {
    fetch(`../handlers/services/services_handler.php?action=get_service_appointments&id=${serviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const appointments = data.data;
                let appointmentsHtml = '<div class="text-start">';
                
                if (appointments.length > 0) {
                    appointmentsHtml += `<p><strong>Appointments for ${serviceName}:</strong></p>`;
                    appointments.forEach(appt => {
                        appointmentsHtml += `
                            <div class="border-bottom pb-2 mb-2">
                                <p><strong>Patient:</strong> ${appt.patient_name}</p>
                                <p><strong>Doctor:</strong> ${appt.doctor_name}</p>
                                <p><strong>Date:</strong> ${new Date(appt.appt_date).toLocaleDateString()} at ${appt.appt_time}</p>
                                <p><strong>Status:</strong> <span class="badge bg-secondary">${appt.stat_name}</span></p>
                            </div>
                        `;
                    });
                } else {
                    appointmentsHtml += '<p class="text-muted">No appointments found for this service.</p>';
                }
                
                appointmentsHtml += '</div>';
                
                Swal.fire({
                    title: `Appointments - ${serviceName}`,
                    html: appointmentsHtml,
                    width: 600,
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        title: 'swal-teal-header'
                    }
                });
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            Swal.fire('Error!', 'Failed to load appointments', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Activate tab based on URL hash on page load
        const hash = window.location.hash;
        if (hash) {
            const tabButton = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
        
        // 2. UPDATE URL WHEN TABS ARE CLICKED MANUALLY 
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                // Update URL hash without page reload
                if (history.pushState) {
                    history.pushState(null, null, target);
                } else {
                    window.location.hash = target;
                }
            });
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>