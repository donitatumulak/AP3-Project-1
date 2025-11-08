<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../handlers/auth/login.php");
    exit();
}

// Include database and classes
require_once '../config/Database.php';
require_once '../classes/users/User.php';
require_once '../classes/users/Staff.php';
require_once '../classes/services/Service.php'; // Add Service class

// Get database connection
$database = new Database();
$db = $database->connect();

// Get classes instances
$staff = new Staff($db);
$user = new User($db);
$service = new Service($db); // Add Service instance

// Get staff's profile information
$staff_id = $_SESSION['profile_id'];
$user_info = $user->getUserWithProfile($_SESSION['user_id']);
$staff_data = $staff->getStaffById($staff_id);

// Get counts for dashboard
// Total doctors
$doctorUsers = $user->getDoctorUsers();
$totalDoctors = $doctorUsers['status'] === 'success' ? count($doctorUsers['data']) : 0;

// Total staff (excluding current staff for count)
$allStaff = $staff->getAllStaff();
$totalStaff = $allStaff['status'] === 'success' ? count($allStaff['data']) : 0;

// Total patients
$patientUsers = $user->getPatientUsers();
$totalPatients = $patientUsers['status'] === 'success' ? count($patientUsers['data']) : 0;

// Total appointments
$appointmentsQuery = "SELECT COUNT(*) as total FROM appointment";
$appointmentsStmt = $db->prepare($appointmentsQuery);
$appointmentsStmt->execute();
$appointmentsResult = $appointmentsStmt->fetch(PDO::FETCH_ASSOC);
$totalAppointments = $appointmentsResult['total'] ?? 0;

// Get services for the table using Service class method
$servicesResult = $service->getServicesWithAppointmentCounts(10);
$services = ($servicesResult['status'] === 'success') ? $servicesResult['data'] : [];

$page = 'dashboards/staff_dashboard';
include '../includes/header.php'; 
?>
<body class="dashboard-page">
  <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar_user.php'; ?>
            <!-- Main Content -->
            <div class="col-lg-10 main-content">
                <!-- Welcome Header -->
                <div class="welcome-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1>Welcome back, <?php echo htmlspecialchars($staff_data['data']['staff_first_name'] . ' ' . $staff_data['data']['staff_last_name']); ?>!</h1>
                            <p class="date-display" id="currentDate"></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fa-solid fa-clipboard-user fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Cards - NOW DYNAMIC -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-1">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL DOCTORS</div>
                                        <div class="card-value"><?php echo $totalDoctors; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL STAFF</div>
                                        <div class="card-value"><?php echo $totalStaff; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-nurse"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL PATIENTS</div>
                                        <div class="card-value"><?php echo $totalPatients; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-user-injured"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card summary-card card-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="card-title">TOTAL APPOINTMENTS</div>
                                        <div class="card-value"><?php echo $totalAppointments; ?></div>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access Buttons -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-3"><i class="fa-solid fa-bolt"></i> Quick Access</h2>
                    </div>
                    <div class="col-3 col-sm-4 mb-3">
                        <a href="../pages/payment_management.php" class="quick-access-btn text-decoration-none">
                            <i class="fa-solid fa-money-bill"></i>
                            <span>Manage Payments</span>
                        </a>
                    </div>
                    <div class="col-3 col-sm-4 mb-3">
                        <a href="../pages/medical_records_management.php" class="quick-access-btn text-decoration-none">
                            <i class="fas fa-file-medical"></i>
                            <span>View Medical Records</span>
                        </a>
                    </div>
                    <div class="col-3 col-sm-4 mb-3">
                        <a href="../pages/services_management.php#specializations" class="quick-access-btn text-decoration-none">
                            <i class="fas fa-stethoscope"></i>
                            <span>Specializations</span>
                        </a>
                    </div>
                </div>       
                <!-- Services Table - CHANGED FROM APPOINTMENTS TO SERVICES -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2><i class="fa-solid fa-concierge-bell"></i> Clinic Services</h2>
                                <a href="../pages/services_management.php#services" class="btn btn-outline dash-btn">Manage All Services</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Service Name</th>
                                            <th>Description</th>
                                            <th>Price</th>
                                            <th>Total Appointments</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($services)): ?>
                                            <?php foreach ($services as $service): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($service['serv_name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $description = $service['serv_description'];
                                                        echo htmlspecialchars(strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="price-tag badge bg-success">
                                                            ₱<?php echo number_format($service['serv_price'], 2); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="appointment-count badge bg-primary">
                                                            <?php echo $service['total_appointments']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <!-- View Details -->
                                                            <button class="btn btn-outline-info btn-action me-1" 
                                                                    title="View Details" 
                                                                    onclick="viewServiceDetails(<?php echo $service['serv_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            
                                                            <!-- View Appointments -->
                                                            <button class="btn btn-outline-primary btn-action me-1" 
                                                                    title="View Appointments" 
                                                                    onclick="viewServiceAppointments(<?php echo $service['serv_id']; ?>)">
                                                                <i class="fas fa-calendar-alt"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No services found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
// Enhanced service action functions
function editService(serviceId) {
    // Redirect to edit service page
    window.location.href = 'edit_service.php?id=' + serviceId;
}

function viewServiceDetails(serviceId) {
    // Show loading
    Swal.fire({
        title: 'Loading Service Details...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('../handlers/get_service_details.php?id=' + serviceId)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.status === 'success' && data.data) {
                const service = data.data;
                Swal.fire({
                    title: service.serv_name,
                    html: `
                        <div class="text-start">
                            <p><strong>Description:</strong><br>${service.serv_description || 'No description available'}</p>
                            <p><strong>Price:</strong> <span class="price-tag badge bg-success">₱${service.serv_price ? Number(service.serv_price).toFixed(2) : '0.00'}</span></p>
                            <p><strong>Service ID:</strong> ${service.serv_id}</p>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonColor: '#17a2b8',
                    width: '500px'
                });
            } else {
                Swal.fire('Error!', data.message || 'Could not load service details', 'error');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error!', 'Network error: ' + error.message, 'error');
        });
}

function viewServiceAppointments(serviceId) {
    // Show loading
    Swal.fire({
        title: 'Loading Appointments...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('../handlers/get_service_appointments.php?id=' + serviceId)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.status === 'success' && data.data) {
                const appointments = data.data;
                
                if (appointments.length > 0) {
                    let appointmentsHtml = '<div class="text-start"><ul class="list-unstyled">';
                    appointments.slice(0, 5).forEach(appt => { // Show first 5 appointments
                        appointmentsHtml += `
                            <li class="mb-2 p-2 border rounded">
                                <strong>${appt.patient_name}</strong><br>
                                <small>Doctor: ${appt.doctor_name}</small><br>
                                <small>Date: ${new Date(appt.appt_date).toLocaleDateString()} at ${appt.appt_time}</small><br>
                                <span class="badge bg-${getStatusBadgeColor(appt.stat_name)}">${appt.stat_name}</span>
                            </li>
                        `;
                    });
                    
                    if (appointments.length > 5) {
                        appointmentsHtml += `<li class="text-muted">... and ${appointments.length - 5} more appointments</li>`;
                    }
                    
                    appointmentsHtml += '</ul></div>';
                    
                    Swal.fire({
                        title: 'Service Appointments',
                        html: appointmentsHtml,
                        icon: 'info',
                        confirmButtonColor: '#007bff',
                        width: '600px'
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Appointments',
                        text: 'No appointments found for this service.',
                        confirmButtonColor: '#6c757d'
                    });
                }
            } else {
                Swal.fire('Error!', data.message || 'Could not load appointments', 'error');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error!', 'Network error: ' + error.message, 'error');
        });
}

// Helper function for status colors
function getStatusBadgeColor(status) {
    const colors = {
        'Scheduled': 'primary',
        'Completed': 'success', 
        'Cancelled': 'danger',
        'Confirmed': 'info'
    };
    return colors[status] || 'secondary';
}

// Display current date
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
});
</script>

<?php include '../includes/footer.php'; ?>