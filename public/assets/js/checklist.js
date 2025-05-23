$(document).ready(function() {
    const modal = $('#taskDetailModal');
    let currentTaskIdForChecklist = null;
    let checklistsLoaded = false;

    function getChecklistApiUrl(action, params = {}) {
        let url;
        switch (action) {
            case 'index':
                url = window.routeUrls.checklistsIndexBase.replace(':taskIdPlaceholder', params.taskId);
                break;
            case 'store':
                url = window.routeUrls.checklistsStoreBase.replace(':taskIdPlaceholder', params.taskId);
                break;
            case 'update':
                url = window.routeUrls.checklistsUpdateBase.replace(':checklistIdPlaceholder', params.checklistId);
                break;
            case 'destroy':
                url = window.routeUrls.checklistsDestroyBase.replace(':checklistIdPlaceholder', params.checklistId);
                break;
            case 'reorder':
                url = window.routeUrls.checklistsReorderBase.replace(':taskIdPlaceholder', params.taskId);
                break;
            default:
                console.error('Invalid checklist API action:', action);
                return null;
        }
        return url;
    }

    function updateChecklistProgress() {
        const $items = modal.find('#modalTaskChecklistsContainer .checklist-item');
        const totalItems = $items.length;
        const doneItems = $items.find('.checklist-item-done:checked').length;
        const $progressText = modal.find('#checklistProgress');

        if (totalItems > 0) {
            const percentage = Math.round((doneItems / totalItems) * 100);
            $progressText.text(`${doneItems}/${totalItems} (${percentage}%)`);
        } else {
            $progressText.text('');
        }
    }


    function renderChecklistItem(item) {
        const checkedAttribute = item.is_done ? 'checked' : '';
        const titleClass = item.is_done ? 'text-decoration-line-through text-muted' : '';
        // Ensure item.title is not undefined or null
        const titleText = item.title || '';
        return `
            <div class="checklist-item d-flex align-items-center mb-2" data-checklist-id="${item.id}">
                <div class="form-check flex-grow-1">
                    <input class="form-check-input checklist-item-done" type="checkbox" ${checkedAttribute} id="checklist-${item.id}" aria-label="Mark item ${titleText} as done">
                    <span class="checklist-item-title ${titleClass}" data-original-title="${escapeHtml(titleText)}">${escapeHtml(titleText)}</span>
                </div>
                <div class="checklist-item-actions">
                    <button class="btn btn-sm btn-outline-secondary checklist-edit-btn py-0 px-1" title="Sửa"><i class="fas fa-pencil-alt fa-xs"></i></button>
                    <button class="btn btn-sm btn-outline-danger checklist-delete-btn py-0 px-1 ml-1" title="Xóa"><i class="fas fa-trash-alt fa-xs"></i></button>
                </div>
                <div class="checklist-item-edit-controls input-group input-group-sm" style="display: none;">
                    <input type="text" class="form-control checklist-item-title-edit" value="${escapeHtml(titleText)}">
                    <div class="input-group-append">
                        <button class="btn btn-sm btn-success checklist-save-edit-btn py-0 px-1" title="Lưu"><i class="fas fa-check fa-xs"></i></button>
                        <button class="btn btn-sm btn-secondary checklist-cancel-edit-btn py-0 px-1 ml-1" title="Hủy"><i class="fas fa-times fa-xs"></i></button>
                    </div>
                </div>
            </div>
        `;
    }

    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    


    function loadChecklists(taskId) {
        currentTaskIdForChecklist = taskId;
        const url = getChecklistApiUrl('index', { taskId: taskId });
        const $checklistContainer = modal.find('#modalTaskChecklistsContainer'); // Target specific container
        const $addForm = modal.find('.add-checklist-item-form');
        $checklistContainer.html('<p class="text-muted small">Đang tải checklist...</p>');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success && response.checklists) {
                    checklistsLoaded = true;
                    if (response.checklists.length > 0) {
                        let checklistHtml = response.checklists.map(renderChecklistItem).join('');
                        $checklistContainer.html(checklistHtml);
                        makeChecklistSortable(taskId);
                    } else {
                        $checklistContainer.html('<p class="text-muted small">Chưa có mục checklist nào.</p>');
                    }
                    $addForm.show(); // Show add form after loading
                    updateChecklistProgress();
                } else {
                    $checklistContainer.html('<p class="text-danger small">Không thể tải checklist.</p>');
                }
            },
            error: function(xhr) {
                console.error('Error loading checklists:', xhr);
                $checklistContainer.html('<p class="text-danger small">Lỗi khi tải checklist.</p>');
            }
        });
    }

    function makeChecklistSortable(taskId) {
        const $checklistItemsWrapper = modal.find('#modalTaskChecklistsContainer');
        if ($checklistItemsWrapper.data('ui-sortable')) {
            $checklistItemsWrapper.sortable('destroy'); // Destroy if already initialized
        }
        $checklistItemsWrapper.sortable({
            items: '.checklist-item',
            handle: '.checklist-item-title',
            axis: 'y',
            update: function(event, ui) {
                const orderedIds = $checklistItemsWrapper.sortable('toArray', { attribute: 'data-checklist-id' });
                const url = getChecklistApiUrl('reorder', { taskId: taskId });
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { ids: orderedIds },
                    success: function(response) {
                        if (!response.success) {
                            console.error('Failed to reorder checklist:', response.message);
                            $checklistItemsWrapper.sortable('cancel');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error reordering checklist:', xhr);
                        $checklistItemsWrapper.sortable('cancel');
                    }
                });
            }
        });
    }

    // Toggle checklist section visibility
   $(document).on('click', '#toggleChecklistVisibilityBtn', function() {
        const $checklistSection = modal.find('#modalTaskChecklistSection');
        $checklistSection.toggle();
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');

        const taskId = $('#modalTaskId').val();
        if ($checklistSection.is(':visible') && taskId && !checklistsLoaded) {
            loadChecklists(taskId);
        }
    });

    // Main trigger from the sidebar
    $(document).on('click', '#modalManageChecklistTrigger', function(e) {
        e.preventDefault();
        const $checklistSection = modal.find('#modalTaskChecklistSection');
        const taskId = $('#modalTaskId').val();

        if (!$checklistSection.is(':visible')) {
            $checklistSection.show();
            modal.find('#toggleChecklistVisibilityBtn i').removeClass('fa-eye').addClass('fa-eye-slash');
        }

        if (taskId && (!checklistsLoaded || taskId !== currentTaskIdForChecklist)) {
            loadChecklists(taskId);
        } else if (!taskId) {
             modal.find('#modalTaskChecklistsContainer').html('<p class="text-danger small">Không thể tải checklist, thiếu ID công việc.</p>');
        }
         // Scroll to checklist section
        modal.find('.modal-body').animate({
            scrollTop: $checklistSection.offset().top - modal.find('.modal-body').offset().top + modal.find('.modal-body').scrollTop() - 10
        }, 300);
    });


    // Add new checklist item
   $(document).on('click', '#saveNewChecklistItemBtn', function() {
        const taskId = $('#modalTaskId').val();
        const $titleInput = $('#newChecklistItemTitle');
        const title = $titleInput.val().trim();

        if (!taskId || !title) {
            alert('Vui lòng nhập tiêu đề cho mục checklist.');
            return;
        }

        const url = getChecklistApiUrl('store', { taskId: taskId });
        $.ajax({
            url: url,
            method: 'POST',
            data: { title: title },
            success: function(response) {
                if (response.success && response.checklist) {
                    const $checklistContainer = modal.find('#modalTaskChecklistsContainer');
                    if ($checklistContainer.find('.checklist-item').length === 0) {
                        $checklistContainer.html(''); // Clear "no items" message
                    }
                    $checklistContainer.append(renderChecklistItem(response.checklist));
                    $titleInput.val('');
                    makeChecklistSortable(taskId);
                    updateChecklistProgress();
                } else {
                    alert('Lỗi: ' + (response.message || 'Không thể thêm mục checklist.'));
                }
            },
            error: function(xhr) {
                console.error('Error adding checklist item:', xhr);
                alert('Lỗi máy chủ khi thêm mục checklist.');
            }
        });
    });
    $(document).on('keypress', '#newChecklistItemTitle', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#saveNewChecklistItemBtn').click();
        }
    });


    // Mark checklist item as done/undone
   $(document).on('change', '.checklist-item-done', function() {
        const $checkbox = $(this);
        const $item = $checkbox.closest('.checklist-item');
        const checklistId = $item.data('checklist-id');
        const isDone = $checkbox.is(':checked');
        const valueToSend = isDone ? 1 : 0;
        const $titleLabel = $item.find('.checklist-item-title');

        const url = getChecklistApiUrl('update', { checklistId: checklistId });
        $.ajax({
            url: url,
            method: 'PUT',
            data: { is_done: valueToSend },
            success: function(response) {
                if (response.success) {
                    $titleLabel.toggleClass('text-decoration-line-through text-muted', isDone);
                    updateChecklistProgress();
                } else {
                    alert('Lỗi: ' + (response.message || 'Không thể cập nhật mục checklist.'));
                    $checkbox.prop('checked', !isDone);
                }
            },
            error: function(xhr) {
                console.error('Error updating checklist item status:', xhr);
                alert('Lỗi máy chủ khi cập nhật trạng thái.');
                $checkbox.prop('checked', !isDone);
            }
        });
    });

    // Edit checklist item title - Show input
   $(document).on('click', '.checklist-edit-btn', function() {
        const $item = $(this).closest('.checklist-item');
        $item.find('.checklist-item-title, .checklist-item-actions').hide();
        $item.find('.checklist-item-edit-controls').show().find('.checklist-item-title-edit').focus();
    });
    $(document).on('dblclick', '.checklist-item-title', function() {
        const $item = $(this).closest('.checklist-item');
        if (!$item.find('.checklist-item-edit-controls').is(':visible')) { // Only if not already editing
            $item.find('.checklist-edit-btn').click();
        }
    });


    // Edit checklist item title - Save
   $(document).on('click', '.checklist-save-edit-btn', function() {
        const $item = $(this).closest('.checklist-item');
        const checklistId = $item.data('checklist-id');
        const $titleSpan = $item.find('.checklist-item-title');
        const $editInput = $item.find('.checklist-item-title-edit');
        const newTitle = $editInput.val().trim();
        const originalTitle = $titleSpan.data('original-title');

        if (!newTitle) {
            alert('Tiêu đề không được để trống.');
            $editInput.val(originalTitle); // Revert in input
            return;
        }

        if (newTitle !== originalTitle) {
            const url = getChecklistApiUrl('update', { checklistId: checklistId });
            $.ajax({
                url: url,
                method: 'PUT',
                data: { title: newTitle },
                success: function(response) {
                    if (response.success && response.checklist) {
                        $titleSpan.text(escapeHtml(response.checklist.title)).data('original-title', escapeHtml(response.checklist.title));
                    } else {
                        alert('Lỗi: ' + (response.message || 'Không thể cập nhật tiêu đề.'));
                        $editInput.val(originalTitle); // Revert in input if save fails
                    }
                },
                error: function(xhr) {
                    console.error('Error updating checklist title:', xhr);
                    alert('Lỗi máy chủ khi cập nhật tiêu đề.');
                    $editInput.val(originalTitle); // Revert in input
                },
                complete: function() {
                    $item.find('.checklist-item-edit-controls').hide();
                    $item.find('.checklist-item-title, .checklist-item-actions').show();
                }
            });
        } else { // No change, just close edit mode
            $item.find('.checklist-item-edit-controls').hide();
            $item.find('.checklist-item-title, .checklist-item-actions').show();
        }
    });

    // Edit checklist item title - Cancel
   $(document).on('click', '.checklist-cancel-edit-btn', function() {
        const $item = $(this).closest('.checklist-item');
        const $titleSpan = $item.find('.checklist-item-title');
        $item.find('.checklist-item-title-edit').val($titleSpan.data('original-title')); // Revert input to original
        $item.find('.checklist-item-edit-controls').hide();
        $item.find('.checklist-item-title, .checklist-item-actions').show();
    });

    // Handle Enter/Escape in edit input
   $(document).on('keypress', '.checklist-item-title-edit', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).closest('.checklist-item').find('.checklist-save-edit-btn').click();
        }
    });
   $(document).on('keydown', '.checklist-item-title-edit', function(e) {
        if (e.key === 'Escape') {
            $(this).closest('.checklist-item').find('.checklist-cancel-edit-btn').click();
        }
    });


    // Delete checklist item
   $(document).on('click', '.checklist-delete-btn', function() {
        if (!confirm('Bạn có chắc chắn muốn xóa mục checklist này?')) {
            return;
        }
        const $item = $(this).closest('.checklist-item');
        const checklistId = $item.data('checklist-id');
        const url = getChecklistApiUrl('destroy', { checklistId: checklistId });

        $.ajax({
            url: url,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    $item.remove();
                    if (modal.find('#modalTaskChecklistsContainer .checklist-item').length === 0) {
                        modal.find('#modalTaskChecklistsContainer').html('<p class="text-muted small">Chưa có mục checklist nào.</p>');
                    }
                    updateChecklistProgress();
                } else {
                    alert('Lỗi: ' + (response.message || 'Không thể xóa mục checklist.'));
                }
            },
            error: function(xhr) {
                console.error('Error deleting checklist item:', xhr);
                alert('Lỗi máy chủ khi xóa mục checklist.');
            }
        });
    });

    // Reset checklist state when modal is hidden or task changes
   $(document).on('hidden.bs.modal', function () {
        resetChecklistState();
    });

   $(document).on('taskChanging', function() { // Custom event you might trigger in task.js before loading new task
        resetChecklistState();
    });
    
    function resetChecklistState() {
        currentTaskIdForChecklist = null;
        checklistsLoaded = false;
        modal.find('#modalTaskChecklistSection').hide();
        modal.find('#toggleChecklistVisibilityBtn i').removeClass('fa-eye-slash').addClass('fa-eye');
        modal.find('#modalTaskChecklistsContainer').html('<p class="text-muted small">Bấm vào nút "Checklist" ở cột phải để quản lý.</p>');
        modal.find('.add-checklist-item-form').hide();
        modal.find('#newChecklistItemTitle').val('');
        updateChecklistProgress(); // Clear progress
    }
     // When a new task is loaded into the modal (triggered from task.js)
   $(document).on('taskLoaded', function(event, taskData) {
        resetChecklistState(); // Reset before deciding to load new ones
        // If you want checklists to auto-show for tasks that have them, add logic here
        // For now, it requires clicking the "Checklist" button in the sidebar.
    });

});