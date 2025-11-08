// management.js - REUSABLE MANAGEMENT FUNCTIONS

// Enhanced hybrid search functionality - auto-detects user tables
function initializeSearch(searchInputId, tableId) {
    const searchInput = document.getElementById(searchInputId);
    const table = document.getElementById(tableId);

    if (searchInput && table) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            let visibleRows = [];

            // Detect if search term is purely numeric (for ID search)
            const isNumericSearch = /^\d+$/.test(searchTerm);
            
            // Auto-detect user management tables
            const isUserTable = tableId.includes('patients-table') || 
                               tableId.includes('staff-table') || 
                               tableId.includes('doctors-table');

            rows.forEach(row => {
                let found = false;
                const cells = row.querySelectorAll('td');

                // 🟢 HYBRID SEARCH FOR USER MANAGEMENT TABLES
                if (isUserTable) {
                    cells.forEach((cell, index) => {
                        const cellText = cell.textContent.toLowerCase().trim();
                        
                        // If numeric search, check only ID column (first column)
                        if (isNumericSearch && index === 0) {
                            const idMatch = cellText.match(/\b\d+\b/);
                            if (idMatch && idMatch[0] === searchTerm) {
                                found = true;
                            }
                        }
                        // If text search, check name columns (typically index 1 and 2)
                        else if (!isNumericSearch && (index === 1 || index === 2)) {
                            if (cellText.includes(searchTerm)) {
                                found = true;
                            }
                        }
                    });
                } 
                // 🩺 SPECIAL HANDLING FOR APPOINTMENTS TABLE
                else if (tableId === 'appointments-table' && isNumericSearch) {
                    // Look for the <small> tag where you show "ID: 25"
                    const smallTag = row.querySelector('td small');
                    if (smallTag) {
                        const match = smallTag.textContent.match(/ID:\s*(\d+)/);
                        if (match && match[1] === searchTerm) {
                            found = true;
                        }
                    }
                }
                // 🔵 NORMAL SEARCH LOGIC FOR OTHER TABLES
                else {
                    cells.forEach((cell, index) => {
                        const cellText = cell.textContent.toLowerCase().trim();

                        // Numeric search = check only first column for exact ID
                        if (isNumericSearch && index === 0) {
                            const idMatch = cellText.match(/\b\d+\b/);
                            if (idMatch && idMatch[0] === searchTerm) {
                                found = true;
                            }
                        }
                        // Text search = partial match across all cells
                        else if (!isNumericSearch && cellText.includes(searchTerm)) {
                            found = true;
                        }
                    });
                }

                // Show/hide the row based on match
                if (searchTerm === '' || found) {
                    row.style.display = '';
                    visibleRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            // Reinitialize pagination with filtered rows
            const paginationId = tableId.replace('-table', '-pagination');
            reinitializePaginationAfterSearch(tableId, paginationId, visibleRows);
        });
    }
}

// ==============================================
// PAGINATION FUNCTIONS
// ==============================================

function initializePagination(tableId, paginationId, itemsPerPage = 10) {
    const table = document.getElementById(tableId);
    const pagination = document.getElementById(paginationId);
    
    if (!table || !pagination) return;
    
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    let currentPage = 1;

    function renderTable() {
        const totalPages = Math.ceil(rows.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = 1;

        // Hide all rows first
        rows.forEach(r => (r.style.display = 'none'));

        // Show only the current page
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        rows.slice(start, end).forEach(r => (r.style.display = ''));

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prev = document.createElement('li');
        prev.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prev.innerHTML = '<a class="page-link" href="#">Previous</a>';
        prev.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        };
        pagination.appendChild(prev);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.onclick = () => {
                currentPage = i;
                renderTable();
            };
            pagination.appendChild(li);
        }

        // Next button
        const next = document.createElement('li');
        next.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        next.innerHTML = '<a class="page-link" href="#">Next</a>';
        next.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        };
        pagination.appendChild(next);
    }

    // Initial render
    renderTable();
}

function reinitializePaginationAfterSearch(tableId, paginationId, visibleRows) {
    const table = document.getElementById(tableId);
    const pagination = document.getElementById(paginationId);
    
    if (!table || !pagination) return;
    
    let currentPage = 1;
    const itemsPerPage = 10;

    function renderTable() {
        const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = 1;

        // Hide all visible rows first
        visibleRows.forEach(r => (r.style.display = 'none'));

        // Show only the current page
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        visibleRows.slice(start, end).forEach(r => (r.style.display = ''));

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prev = document.createElement('li');
        prev.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prev.innerHTML = '<a class="page-link" href="#">Previous</a>';
        prev.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        };
        pagination.appendChild(prev);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.onclick = () => {
                currentPage = i;
                renderTable();
            };
            pagination.appendChild(li);
        }

        // Next button
        const next = document.createElement('li');
        next.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        next.innerHTML = '<a class="page-link" href="#">Next</a>';
        next.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        };
        pagination.appendChild(next);
    }

    // Initial render
    renderTable();
}

// ==============================================
// USER MANAGEMENT SPECIFIC FUNCTIONS
// ==============================================

/**
 * Initialize user type filter functionality
 */
function initializeUserFilters() {
    const filterRadios = document.querySelectorAll('input[name="userTypeFilter"]');
    const usersTable = document.getElementById('users-table');
    
    if (!usersTable) return;
    
    filterRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const filterValue = this.id.replace('filter-', '');
            filterUsersByType(filterValue);
        });
    });
}

/**
 * Filter users by type (doctor, patient, staff, all)
 */
function filterUsersByType(filterType) {
    const rows = document.querySelectorAll('#users-table tbody tr');
    
    rows.forEach(row => {
        const userType = row.getAttribute('data-user-type');
        
        switch(filterType) {
            case 'all':
                row.style.display = '';
                break;
            case 'doctors':
                row.style.display = userType === 'doctor' ? '' : 'none';
                break;
            case 'patients':
                row.style.display = userType === 'patient' ? '' : 'none';
                break;
            case 'staff':
                row.style.display = userType === 'staff' ? '' : 'none';
                break;
            default:
                row.style.display = '';
        }
    });
    
    // Update pagination after filtering
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    reinitializePaginationAfterSearch('users-table', 'users-pagination', visibleRows);
}

// ==============================================
// OTHER REUSABLE FUNCTIONS
// ==============================================

// Delete confirmation - works for any item
function confirmDelete(itemId, itemName, itemType, deleteUrl) {
    Swal.fire({
        title: `Delete ${itemType.replace('_', ' ')}?`,
        html: `Are you sure you want to delete <strong>${itemName}</strong>?<br>This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;

            // ✅ Match correct case names & ID field names
            const actionName =
                itemType === 'doc' ? 'delete_doctor' :
                itemType === 'pat' ? 'delete_patient' :
                itemType === 'staff' ? 'delete_staff' :
                itemType === 'appointment' ? 'delete_appointment' :
                itemType === 'schedule' ? 'delete_schedule' :
                itemType === 'status' ? 'delete_status' :
                itemType === 'medical_record' ? 'delete_medical_record' : 
                itemType === 'payment' ? 'delete_payment' :
                itemType === 'payment_method' ? 'delete_payment_method' : 
                itemType === 'payment_status' ? 'delete_payment_status' :
                itemType === 'service' ? 'delete_service' : 
                itemType === 'specialization' ? 'delete_specialization' : 
                'delete_' + itemType;

            const idFieldName =
                itemType === 'doc' ? 'doc_id' :
                itemType === 'pat' ? 'pat_id' :
                itemType === 'staff' ? 'staff_id' :
                itemType === 'appointment' ? 'appt_id' :
                itemType === 'schedule' ? 'sched_id' :
                itemType === 'status' ? 'stat_id' :
                itemType === 'medical_record' ? 'med_rec_id' : 
                itemType === 'payment' ? 'pymt_id' :
                itemType === 'payment_method' ? 'pymt_meth_id' : 
                itemType === 'payment_status' ? 'pymt_stat_id' : 
                itemType === 'service' ? 'serv_id' : 
                itemType === 'specialization' ? 'spec_id' : 
                `${itemType}_id`;

            // ✅ Build the form dynamically
            form.innerHTML = `
                <input type="hidden" name="action" value="${actionName}">
                <input type="hidden" name="${idFieldName}" value="${itemId}">
            `;

            document.body.appendChild(form);
            form.submit();
        }
    });
}


// View details modal - With field filtering
function viewItemDetails(itemId, itemType, detailsUrl, customLabels = null, allowedFields = null) {
    fetch(`${detailsUrl}?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const item = data.data;
                
                let detailsHtml = `<div class="text-start">`;
                
                // Get the fields to display
                let fieldsToDisplay = allowedFields;
                if (!fieldsToDisplay) {
                    // If no allowedFields provided, use all fields except excluded ones
                    fieldsToDisplay = Object.keys(item).filter(key => 
                        !['password', 'token', 'created_at', 'updated_at'].includes(key)
                    );
                }
                
                fieldsToDisplay.forEach(key => {
                    if (item[key] !== null && item[key] !== '' && item[key] !== undefined) {
                        const label = customLabels?.[key] || formatTitle(key);
                        const value = formatFieldValue(key, item[key]);
                        detailsHtml += `<p><strong>${label}:</strong> ${value}</p>`;
                    }
                });
                
                detailsHtml += `</div>`;
                
                Swal.fire({
                    title: `${formatTitle(itemType)} Details`,
                    html: detailsHtml,
                    width: 600,
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'custom-swal-popup',
                        header: 'custom-swal-header-teal',
                        title: 'custom-swal-title',
                        closeButton: 'custom-swal-close'
                    }
                });
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading details:', error);
            Swal.fire('Error!', 'Failed to load details', 'error');
        });
}

// Helper functions
function formatTitle(text) {
    return text
        .split(/[\s_]+/)
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}

function formatFieldValue(key, value) {
    // Format dates
    if (key.includes('date') || key.includes('_at')) {
        return new Date(value).toLocaleDateString();
    }
    
    // Format long text
    if (typeof value === 'string' && value.length > 100) {
        return `<div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; border-radius: 4px; background: #f9f9f9;">${value}</div>`;
    }
    
    return value;
}

// Initialize all management features
function initializeManagementFeatures(config = {}) {
    const defaultConfig = {
        searchInputId: 'search-items',
        tableId: 'items-table',
        paginationId: 'items-pagination',
        itemsPerPage: 10
    };
    
    const finalConfig = { ...defaultConfig, ...config };
    
    // Initialize search
    initializeSearch(finalConfig.searchInputId, finalConfig.tableId);
    
    // Initialize pagination
    initializePagination(finalConfig.tableId, finalConfig.paginationId, finalConfig.itemsPerPage);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Doctor Appointments Search Function
function searchDoctorAppointments() {
    const searchTerm = document.getElementById('search-doctor-appointments').value.trim();
    
    if (!searchTerm) {
        Swal.fire('Info', 'Please enter a doctor name to search', 'info');
        return;
    }

    // Show loading, hide instructions, show filters
    document.getElementById('doctorAppointmentsInstructions').style.display = 'none';
    document.getElementById('timeFiltersSection').style.display = 'block';
    document.getElementById('doctorAppointmentsResults').style.display = 'block';
    document.getElementById('doctor-appointments-tbody').innerHTML = '<tr><td colspan="7" class="text-center py-4">Loading appointments...</td></tr>';

    // AJAX call to search doctor appointments
    fetch(`../handlers/appointments/doctor_appointments_handler.php?action=search_doctor&name=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.length > 0) {
                renderDoctorAppointmentsTable(data.data);
                initializePagination('doctor-appointments-table', 'doctor-appointments-pagination', 10);
                document.getElementById('doctorAppointmentsPaginationContainer').style.display = 'block';
            } else {
                document.getElementById('doctor-appointments-tbody').innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-user-md fa-2x mb-2 d-block"></i>
                            ${data.message || 'No appointments found for this doctor.'}
                        </td>
                    </tr>
                `;
                document.getElementById('doctorAppointmentsPaginationContainer').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('doctor-appointments-tbody').innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error loading appointments</td></tr>';
            document.getElementById('doctorAppointmentsPaginationContainer').style.display = 'none';
        });
}

// Render doctor appointments table
function renderDoctorAppointmentsTable(appointments) {
    const tbody = document.getElementById('doctor-appointments-tbody');
    tbody.innerHTML = '';

    appointments.forEach(appt => {
        const row = document.createElement('tr');
        row.setAttribute('data-appointment-date', appt.appt_date);
        row.setAttribute('data-doctor-name', `${appt.doc_first_name} ${appt.doc_last_name}`);
        row.setAttribute('data-patient-name', `${appt.pat_first_name} ${appt.pat_last_name}`);
        
        // Determine status class
        let statusClass = 'bg-secondary';
        if (appt.stat_name === 'Scheduled') statusClass = 'pastel-orange';
        else if (appt.stat_name === 'Cancelled') statusClass = 'pastel-pink';
        else if (appt.stat_name === 'Completed') statusClass = 'pastel-blue';
        else if (appt.stat_name === 'Confirmed') statusClass = 'pastel-green';

        row.innerHTML = `
            <td>
                <strong class="badge pastel-green">${appt.formatted_appt_id}</strong>
                <small class="text-muted d-block">ID: ${appt.appt_id}</small>
            </td>
            <td>
                <strong>${appt.pat_first_name} ${appt.pat_last_name}</strong>
            </td>
            <td>
                <span class="text-teal">Dr. ${appt.doc_first_name} ${appt.doc_last_name}</span>
            </td>
            <td>
                <small>${appt.serv_name}</small>
            </td>
            <td>
                <strong>${new Date(appt.appt_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</strong>
                <small class="text-muted d-block">${new Date('1970-01-01T' + appt.appt_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}</small>
            </td>
            <td>
                <span class="badge ${statusClass}">${appt.stat_name}</span>
            </td>
            <td class="align-middle">
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-primary btn-action"
                             onclick="updateDoctorAppointmentStatus(${appt.appt_id})"
                            title="Update Status">
                        <i class="fas fa-sync"></i>
                    </button>
                    <button class="btn btn-outline-info btn-action"
                             onclick="viewAppointmentDetails(${appt.appt_id})"
                            title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Initialize doctor appointment filters
function initializeDoctorAppointmentFilters() {
    const filterRadios = document.querySelectorAll('input[name="apptTimeFilter"]');
    
    filterRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const filterValue = this.id.replace('filter-', '').replace('-appts', '');
            filterDoctorAppointmentsByTime(filterValue);
        });
    });
}

function filterDoctorAppointmentsByTime(filterType) {
    const rows = document.querySelectorAll('#doctor-appointments-table tbody tr');
    const today = new Date().toISOString().split('T')[0];
    
    rows.forEach(row => {
        const appointmentDate = row.getAttribute('data-appointment-date');
        let shouldShow = false;
        
        switch(filterType) {
            case 'all':
                shouldShow = true;
                break;
            case 'today':
                shouldShow = appointmentDate === today;
                break;
            case 'future':
                shouldShow = appointmentDate > today;
                break;
            case 'past':
                shouldShow = appointmentDate < today;
                break;
            default:
                shouldShow = true;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    // Update pagination after filtering
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    reinitializePaginationAfterSearch('doctor-appointments-table', 'doctor-appointments-pagination', visibleRows);
}

// Reset to default state when search input is cleared
function initializeDoctorAppointmentsSearchReset() {
    const searchInput = document.getElementById('search-doctor-appointments');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // If search input is empty, reset to default state
            if (this.value.trim() === '') {
                resetDoctorAppointmentsToDefault();
            }
        });
    }
}

// Reset function
function resetDoctorAppointmentsToDefault() {
    // Hide results and filters
    document.getElementById('doctorAppointmentsResults').style.display = 'none';
    document.getElementById('timeFiltersSection').style.display = 'none';
    document.getElementById('doctorAppointmentsPaginationContainer').style.display = 'none';
    
    // Show instructions
    document.getElementById('doctorAppointmentsInstructions').style.display = 'block';
    
    // Clear the table
    document.getElementById('doctor-appointments-tbody').innerHTML = '';
    
    // Reset time filter to "All"
    const allFilter = document.getElementById('filter-all-appts');
    if (allFilter) {
        allFilter.checked = true;
    }
}

document.addEventListener("click", function (e) {
    if (e.target.matches('[data-bs-toggle="tab"]')) {
        const tabId = e.target.getAttribute("data-bs-target");
        localStorage.setItem("activeTab", tabId);
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const activeTab = localStorage.getItem("activeTab");
    if (activeTab) {
        const tabElement = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }
});
