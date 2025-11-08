<?php
session_start();
$page = 'pages/my_schedule';
require_once '../config/Database.php';
require_once '../classes/appointments/Schedule.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_user.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    echo "<div class='alert alert-danger text-center m-4'>Access denied. Doctors only.</div>";
    require_once '../includes/footer.php';
    exit();
}

$database = new Database();
$db = $database->connect();
$schedule = new Schedule($db);

$doc_id = $_SESSION['profile_id'];
?>

<body class="account-page">
<div class="main-content p-4">
    <div class="container">

        <!-- HEADER CARD -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 bg-teal text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="d-flex align-items-center mb-1">
                        <h3 class="fw-bold mb-0 text-white">My Schedule</h3>
                        <span class="text-light ms-3 fs-6">
                            <?= date('l, F j, Y'); ?>
                        </span>
                    </div>
                    <p class="mb-0">View and manage your working hours</p>
                </div>
                <i class="fas fa-calendar-alt fa-3x opacity-75"></i>
            </div>
        </div>

         <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                         <!-- Tabs -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                    <ul class="nav nav-tabs" id="scheduleTabs" role="tablist">
                                        <li class="nav-item">
                                            <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button">
                                                Today's Schedule
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                                                All Schedule
                                            </button>
                                        </li>
                                    </ul>
                                    <!-- ✅ Add Schedule button beside tabs -->
                                    <button class="btn btn-teal btn-sm" onclick="openAddModal()">
                                        <i class="fas fa-plus-circle"></i> Add Schedule
                                    </button>
                                </div>

                                <div class="tab-content">

                                <!-- TODAY SCHEDULE -->
                                <div class="tab-pane fade show active" id="today">
                                    <div class="card border-0 rounded-4 mb-3">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0" id="todayTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Start Time</th>
                                                            <th>End Time</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                    </div>
                                </div>

                                <!-- ALL SCHEDULES -->
                                <div class="tab-pane fade" id="all">
                                    <div class="card border-0 rounded-4 mb-3">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0" id="allTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Start Time</th>
                                                            <th>End Time</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
         </div>
    </div>
</div>

<script>
// Load schedules dynamically
document.addEventListener('DOMContentLoaded', () => {
    loadSchedules('get_today', '#todayTable tbody');
    loadSchedules('get_all', '#allTable tbody');
});

function loadSchedules(action, tableSelector) {
    fetch(`../handlers/schedule_handler.php?action=${action}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector(tableSelector);
            tbody.innerHTML = '';

            if (data.status === 'success' && data.data.length > 0) {
                data.data.forEach(row => {
                    tbody.innerHTML += `
                        <tr>
                            <td>
                                <span class="badge pastel-blue">${row.sched_days}</span>
                            </td>
                            <td>${formatTime(row.sched_start_time)}</td>
                            <td>${formatTime(row.sched_end_time)}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary me-1" onclick="openEditModal(${row.sched_id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteSchedule(${row.sched_id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No schedules found.</td></tr>`;
            }
        });
}

function formatTime(time) {
    return new Date('1970-01-01T' + time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Add Schedule
function openAddModal() {
    Swal.fire({
        title: 'Add Schedule',
        html: `
            <select id="day" class="form-select mb-2">
                <option value="">Select Day</option>
                <option>Monday</option><option>Tuesday</option>
                <option>Wednesday</option><option>Thursday</option>
                <option>Friday</option><option>Saturday</option><option>Sunday</option>
            </select>
            <input type="time" id="start" class="form-control mb-2" placeholder="Start time">
            <input type="time" id="end" class="form-control" placeholder="End time">
        `,
        showCancelButton: true,
        confirmButtonText: 'Save',
        preConfirm: () => {
            return {
                sched_days: document.getElementById('day').value,
                sched_start_time: document.getElementById('start').value,
                sched_end_time: document.getElementById('end').value
            };
        }
    }).then(result => {
        if (result.isConfirmed) {
            fetch('../handlers/schedule_handler.php?action=add', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(result.value)
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire(data.status === 'success' ? 'Success' : 'Error', data.message, data.status);
                loadSchedules('get_today', '#todayTable tbody');
                loadSchedules('get_all', '#allTable tbody');
            });
        }
    });
}

// Edit Schedule
function openEditModal(id) {
    fetch(`../handlers/schedule_handler.php?action=get&sched_id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') return Swal.fire('Error', 'Schedule not found', 'error');

            const sched = data.data;
            Swal.fire({
                title: 'Edit Schedule',
                html: `
                    <select id="day" class="form-select mb-2">
                        <option ${sched.sched_days === 'Monday' ? 'selected' : ''}>Monday</option>
                        <option ${sched.sched_days === 'Tuesday' ? 'selected' : ''}>Tuesday</option>
                        <option ${sched.sched_days === 'Wednesday' ? 'selected' : ''}>Wednesday</option>
                        <option ${sched.sched_days === 'Thursday' ? 'selected' : ''}>Thursday</option>
                        <option ${sched.sched_days === 'Friday' ? 'selected' : ''}>Friday</option>
                        <option ${sched.sched_days === 'Saturday' ? 'selected' : ''}>Saturday</option>
                        <option ${sched.sched_days === 'Sunday' ? 'selected' : ''}>Sunday</option>
                    </select>
                    <input type="time" id="start" class="form-control mb-2" value="${sched.sched_start_time}">
                    <input type="time" id="end" class="form-control" value="${sched.sched_end_time}">
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                preConfirm: () => {
                    return {
                        sched_id: id,
                        sched_days: document.getElementById('day').value,
                        sched_start_time: document.getElementById('start').value,
                        sched_end_time: document.getElementById('end').value
                    };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('../handlers/schedule_handler.php?action=update', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(result.value)
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire(data.status === 'success' ? 'Success' : 'Error', data.message, data.status);
                        loadSchedules('get_today', '#todayTable tbody');
                        loadSchedules('get_all', '#allTable tbody');
                    });
                }
            });
        });
}

// Delete Schedule
function deleteSchedule(id) {
    Swal.fire({
        title: 'Delete Schedule?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#d33'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('../handlers/schedule_handler.php?action=delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ sched_id: id })
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire(data.status === 'success' ? 'Deleted' : 'Error', data.message, data.status);
                loadSchedules('get_today', '#todayTable tbody');
                loadSchedules('get_all', '#allTable tbody');
            });
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
