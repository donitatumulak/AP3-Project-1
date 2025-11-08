<?php
if (!isset($user_type) || !isset($table_data)) {
    $user_type = $user_type ?? '';
    $table_data = $table_data ?? [];
}
?>

<div class="management-table-container">
    <!-- Table Header with Search and Actions -->
    <div class="management-table-header d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
        <div class="search-section">
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" 
                       placeholder="Search <?php echo $user_type; ?>s..." 
                       onkeyup="searchUsers('<?php echo $user_type; ?>', this.value)">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="action-section">
            <button class="btn btn-teal" onclick="openAddModal('<?php echo $user_type; ?>')">
                <i class="fas fa-plus"></i> Add <?php echo ucfirst($user_type); ?>
            </button>
        </div>
    </div>

    <!-- User Table -->
    <div class="table-responsive">
        <table class="table table-hover" id="<?php echo $user_type; ?>-table">
            <thead class="table-teal">
                <tr>
                    <?php if ($user_type === 'doctor'): ?>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Actions</th>
                    <?php elseif ($user_type === 'patient'): ?>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Actions</th>
                    <?php else: ?>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($table_data)): ?>
                    <?php foreach ($table_data as $user): ?>
                    <tr>
                        <?php if ($user_type === 'doctor'): ?>
                            <td><?php echo $user['doc_id']; ?></td>
                            <td>
                                <strong>
                                    <?php 
                                    $full_name = $user['doc_first_name'] . ' ' . $user['doc_last_name'];
                                    echo htmlspecialchars($full_name); 
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['doc_contact_num'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['doc_email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['spec_name'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-warning btn-action" 
                                            onclick="openEditModal('<?php echo $user_type; ?>', <?php echo $user['doc_id']; ?>)"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-action"
                                            onclick="deleteUser('<?php echo $user_type; ?>', <?php echo $user['doc_id']; ?>, '<?php echo htmlspecialchars($full_name); ?>')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        <?php elseif ($user_type === 'patient'): ?>
                            <td><?php echo $user['pat_id']; ?></td>
                            <td>
                                <strong>
                                    <?php 
                                    $full_name = $user['pat_first_name'] . ' ' . $user['pat_last_name'];
                                    echo htmlspecialchars($full_name); 
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['pat_dob'])); ?></td>
                            <td><?php echo htmlspecialchars($user['pat_gender']); ?></td>
                            <td><?php echo htmlspecialchars($user['pat_contact_num'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['pat_email'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-warning btn-action" 
                                            onclick="openEditModal('<?php echo $user_type; ?>', <?php echo $user['pat_id']; ?>)"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-action"
                                            onclick="deleteUser('<?php echo $user_type; ?>', <?php echo $user['pat_id']; ?>, '<?php echo htmlspecialchars($full_name); ?>')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        <?php else: ?>
                            <td><?php echo $user['staff_id']; ?></td>
                            <td>
                                <strong>
                                    <?php 
                                    $full_name = $user['staff_first_name'] . ' ' . $user['staff_last_name'];
                                    echo htmlspecialchars($full_name); 
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['staff_contact_num'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['staff_email'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-warning btn-action" 
                                            onclick="openEditModal('<?php echo $user_type; ?>', <?php echo $user['staff_id']; ?>)"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-action"
                                            onclick="deleteUser('<?php echo $user_type; ?>', <?php echo $user['staff_id']; ?>, '<?php echo htmlspecialchars($full_name); ?>')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="no-results-<?php echo $user_type; ?>">
                        <td colspan="<?php echo $user_type === 'patient' ? '7' : ($user_type === 'doctor' ? '6' : '5'); ?>" 
                            class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            No <?php echo $user_type; ?>s found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($table_data) && count($table_data) > 10): ?>
    <nav aria-label="<?php echo ucfirst($user_type); ?> pagination">
        <ul class="pagination justify-content-center mt-3" id="<?php echo $user_type; ?>-pagination">
            <!-- Pagination will be generated by JavaScript -->
        </ul>
    </nav>
    <?php endif; ?>
</div>