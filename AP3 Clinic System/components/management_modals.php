<?php
/**
 * REUSABLE MODAL TEMPLATES
 * Usage: include this file and call the functions below
 */

// Add Item Modal - Dynamic form fields
function renderAddModal($modalId, $title, $formAction, $itemType, $fields) {
    ?>
    <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-labelledby="<?php echo $modalId; ?>Label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title" id="<?php echo $modalId; ?>Label">Add New <?php echo $title; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $formAction; ?>">
                    <input type="hidden" name="action" value="add_<?php echo $itemType; ?>">
                    
                    <div class="modal-body">
                        <div class="row">
                            <?php foreach ($fields as $field): ?>
                                <div class="<?php echo $field['width'] ?? 'col-12'; ?> mb-3">
                                    <label for="<?php echo $field['name']; ?>" class="form-label">
                                        <?php echo $field['label']; ?><?php echo $field['required'] ? ' *' : ''; ?>
                                    </label>

                                    <?php
                                    $required = $field['required'] ? 'required' : '';
                                    $readonly = isset($field['readonly']) && $field['readonly'] ? 'readonly' : '';
                                    ?>

                                    <?php if ($field['type'] === 'textarea'): ?>
                                        <textarea class="form-control" id="<?php echo $field['name']; ?>" 
                                                  name="<?php echo $field['name']; ?>" 
                                                  rows="<?php echo $field['rows'] ?? 3; ?>"
                                                  <?php echo $required; ?>
                                                  placeholder="<?php echo $field['placeholder'] ?? ''; ?>"><?php echo $field['value'] ?? ''; ?></textarea>

                                    <?php elseif ($field['type'] === 'select'): ?>
                                        <select class="form-select" id="<?php echo $field['name']; ?>" 
                                                name="<?php echo $field['name']; ?>" <?php echo $required; ?>>
                                            <option value="">Select <?php echo htmlspecialchars($field['label']); ?></option>
                                            <?php if (!empty($field['options'])): ?>
                                                <?php foreach ($field['options'] as $option): ?>
                                                    <?php
                                                        // Dynamically detect key/value based on table type
                                                        $optionId = $option['pymt_meth_id'] 
                                                            ?? $option['pymt_stat_id'] 
                                                            ?? $option['doc_id'] 
                                                            ?? $option['id'] 
                                                            ?? '';
                                                        $optionName = $option['pymt_meth_name'] 
                                                            ?? $option['pymt_stat_name'] 
                                                            ?? $option['name'] 
                                                            ?? '';
                                                    ?>
                                                    <option value="<?php echo htmlspecialchars($optionId); ?>">
                                                        <?php echo htmlspecialchars($optionName); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>

                                    <?php else: ?>
                                        <input type="<?php echo $field['type']; ?>" 
                                               class="form-control" 
                                               id="<?php echo $field['name']; ?>" 
                                               name="<?php echo $field['name']; ?>"
                                               value="<?php echo $field['value'] ?? ''; ?>"
                                               <?php echo $required; ?> <?php echo $readonly; ?>
                                               placeholder="<?php echo $field['placeholder'] ?? ''; ?>"
                                               <?php echo isset($field['step']) ? 'step="'.$field['step'].'"' : ''; ?>>
                                    <?php endif; ?>

                                    <?php if (!empty($field['help'])): ?>
                                        <small class="form-text text-muted"><?php echo $field['help']; ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal">Add <?php echo $title; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

// Edit Item Modal - Loads via AJAX
function renderEditModal($modalId, $title, $itemType) {
    ?>
    <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-labelledby="<?php echo $modalId; ?>Label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title" id="<?php echo $modalId; ?>Label">Edit <?php echo $title; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="<?php echo $modalId; ?>Body">
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
    <?php
}
?>
