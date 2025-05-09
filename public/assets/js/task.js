// D:\Đồ án tốt nghiệp\projects\datn_kanban_list\public\assets\js\task.js
// Tạo một namespace để tránh xung đột và dễ gọi từ column.js
var TaskJS = (function ($) {
    let isDragging = false;
    let currentOpenTaskId = null; // ID của task đang mở trong modal
    let originalTaskData = {}; // Dữ liệu gốc của task trong modal
    // --- Helper Functions (Lấy từ column.js hoặc định nghĩa lại nếu cần) ---
    function showNotification(message, type = 'success') {
        alert(type.toUpperCase() + ": " + message);
        console.log(type.toUpperCase() + ": " + message);
    }

    function getRoute(routeName, params = {}) {
        if (typeof window.routeUrls === 'undefined' || !window.routeUrls) {
            console.error("window.routeUrls is not defined!");
            return '#ROUTE_ERROR';
        }
        let url = window.routeUrls[routeName] || '';
        if (!url) {
            console.error(`Route "${routeName}" not found in window.routeUrls.`);
            return '#ROUTE_NOT_FOUND';
        }
        for (const key in params) {
            const placeholder = `:${key}Placeholder`;
            const placeholderSimple = `:${key}`;
            url = url.replace(new RegExp(placeholder, 'g'), params[key]);
            url = url.replace(new RegExp(placeholderSimple, 'g'), params[key]);
        }
        if ((url.includes(':boardIdPlaceholder') || url.includes(':boardId')) && !params['boardId'] && window.currentBoardId) {
            url = url.replace(/:boardId(Placeholder)?/g, window.currentBoardId);
        }
        if (url.includes(':')) {
            console.warn(`Potential unresolved placeholder in URL for route "${routeName}": ${url}`);
        }
        return url;
    }


    function createTaskCardHtml(task) {
        let assigneesHtml = '';
        if (task.assignees && task.assignees.length > 0) {
            assigneesHtml = '<div class="task-assignees mt-2">';
            task.assignees.slice(0, 4).forEach(assignee => {
                assigneesHtml += `<img src="https://i.pravatar.cc/40?u=${assignee.email}"
                                     class="rounded-circle border border-white"
                                     width="25" height="25"
                                     title="${assignee.name}"
                                     alt="${assignee.name}">`;
            });
            if (task.assignees.length > 4) {
                assigneesHtml += `<span class="avatar-more ml-n2 border border-light rounded-circle bg-light text-muted small d-inline-flex align-items-center justify-content-center"
                                       style="width: 25px; height: 25px; font-size: 0.7rem;"
                                       title="${task.assignees.length - 4} more assignees">
                                       +${task.assignees.length - 4}
                                </span>`;
            }
            assigneesHtml += '</div>';
        }

        let dueDateHtml = '';
        if (task.due_date) {
            dueDateHtml = `<small class="text-muted d-block mb-1">
                               <i class="far fa-calendar-alt mr-1"></i> Due: ${task.formatted_due_date || task.due_date}
                             </small>`;
        }
        // Thêm class 'task-action-trigger' để có thể gắn event mở modal
        return `
            <div class="kanban-card task-action-trigger" data-task-id="${task.id}">
                <h5 class="task-title mb-1">${task.title}</h5>
                ${dueDateHtml}
                ${assigneesHtml}
                ${task.description ? `<p class="small text-muted mb-1 task-description-preview">${task.description.substring(0, 50)}...</p>` : ''}
            </div>
        `;
    }


    // --- Initialize Card Sortable (cho tất cả .column-content hiện có) ---
    function initializeCardSortableForAllColumns() {
        $(".column-content").sortable({
            connectWith: ".column-content",
            items: "> .kanban-card:not(.add-card-placeholder):not(.new-card-entry)",
            placeholder: "kanban-placeholder",
            forcePlaceholderSize: true,
            tolerance: "pointer",
            start: function (event, ui) {
                ui.item.addClass('dragging');
                ui.placeholder.height(ui.item.outerHeight());
            },
            stop: function (event, ui) {
                ui.item.removeClass('dragging');
                const $columnContent = ui.item.closest('.column-content');
                const $addCardPlaceholder = $columnContent.find('.add-card-placeholder');
                $columnContent.append($addCardPlaceholder); // Đảm bảo nút "Thêm" luôn ở cuối

                let taskId = ui.item.data('task-id');
                let newColumnId = $columnContent.data('column-id');
                let taskOrderInNewColumn = $columnContent.children('.kanban-card:not(.add-card-placeholder):not(.new-card-entry)').map(function () {
                    return $(this).data('task-id');
                }).get();

                const url = getRoute('tasksUpdatePosition');
                if (url.startsWith('#ROUTE_')) {
                    showNotification('Lỗi cấu hình route cập nhật vị trí task.', 'error');
                    $(".column-content").sortable("cancel");
                    return;
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        task_id: taskId,
                        new_column_id: newColumnId,
                        order: taskOrderInNewColumn
                    },
                    success: function (response) {
                        if (response.success) {
                            // showNotification(response.message || 'Vị trí task đã được cập nhật.');
                        } else {
                            showNotification(response.message || 'Không thể cập nhật vị trí task.', 'error');
                            $(".column-content").sortable("cancel");
                        }
                    },
                    error: function (jqXHR) {
                        showNotification(`Lỗi cập nhật vị trí: ${jqXHR.responseJSON?.message || jqXHR.statusText}`, 'error');
                        $(".column-content").sortable("cancel");
                    }
                });
            }
        }).disableSelection();
    }

    // Hàm này sẽ được gọi từ column.js khi một cột mới được tạo
    function initializeSortableForColumn($columnContentElement) {
        if ($columnContentElement && $columnContentElement.length) {
            // Hủy sortable cũ nếu có (để tránh gắn nhiều lần) và khởi tạo lại
            // Tuy nhiên, jQuery UI sortable thường tự xử lý việc này nếu selector không đổi
            // Chỉ cần đảm bảo connectWith đúng
            $columnContentElement.sortable({
                connectWith: ".column-content", // Quan trọng: phải kết nối với các cột khác
                items: "> .kanban-card:not(.add-card-placeholder):not(.new-card-entry)",
                placeholder: "kanban-placeholder",
                forcePlaceholderSize: true,
                tolerance: "pointer",
                start: function (event, ui) { ui.item.addClass('dragging'); ui.placeholder.height(ui.item.outerHeight()); },
                stop: function (event, ui) {
                    ui.item.removeClass('dragging');
                    const $columnContent = ui.item.closest('.column-content');
                    const $addCardPlaceholder = $columnContent.find('.add-card-placeholder');
                    $columnContent.append($addCardPlaceholder);

                    let taskId = ui.item.data('task-id');
                    let newColumnId = $columnContent.data('column-id');
                    let taskOrderInNewColumn = $columnContent.children('.kanban-card:not(.add-card-placeholder):not(.new-card-entry)').map(function () {
                        return $(this).data('task-id');
                    }).get();

                    const url = getRoute('tasksUpdatePosition');
                    if (url.startsWith('#ROUTE_')) { /* ... xử lý lỗi ... */ return; }
                    $.ajax({ /* ... AJAX call như trên ... */ });
                }
            }).disableSelection();
            console.log('Sortable initialized for new column content:', $columnContentElement);
        }
    }


    // --- Card Adding ---
    $('#kanbanBoard').on('click', '.add-card-placeholder', function () {
        const $placeholder = $(this);
        const $columnContent = $placeholder.closest('.column-content');

        const newCardInputHtml = `
        <div class="kanban-card new-card-entry p-2">
          <textarea class="form-control card-input mb-2" rows="2" placeholder="Nhập tiêu đề công việc..."></textarea>
          <div class="mt-1">
             <button class="btn btn-success btn-sm save-card-btn">Lưu</button>
             <button class="btn btn-secondary btn-sm cancel-card-btn ml-1">Hủy</button>
          </div>
        </div>
        `;
        $(newCardInputHtml).insertBefore($placeholder);
        $placeholder.hide();
        $columnContent.find('.card-input').last().focus();
    });

    $('#kanbanBoard').on('click', '.cancel-card-btn', function () {
        const $entry = $(this).closest('.new-card-entry');
        const $placeholder = $entry.siblings('.add-card-placeholder');
        $entry.remove();
        $placeholder.show();
    });

    $('#kanbanBoard').on('click', '.save-card-btn', function () {
        const $button = $(this);
        const $entry = $button.closest('.new-card-entry');
        const $input = $entry.find('.card-input');
        const cardTitle = $input.val().trim();
        const $columnContent = $entry.closest('.column-content');
        const columnId = $columnContent.data('column-id'); // Lấy columnId từ data attribute

        if (!cardTitle) {
            showNotification('Tiêu đề công việc không được để trống.', 'warning');
            $input.focus();
            return;
        }

        if (!columnId) { // Kiểm tra columnId
            showNotification('Lỗi: Không tìm thấy ID cột để thêm công việc.', 'error');
            return;
        }

        const url = getRoute('tasksStoreBase', { columnId: columnId }); // Đảm bảo columnId được truyền vào
        if (url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route tạo task.', 'error');
            return;
        }

        $button.prop('disabled', true).text('Đang lưu...');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                title: cardTitle,
                // Bạn có thể gửi thêm các trường khác nếu form nhập liệu có, ví dụ:
                // description: $entry.find('.card-description-input').val(),
                _token: $('meta[name="csrf-token"]').attr('content') // Quan trọng: gửi CSRF token
            },
            success: function (response) {
                if (response.success && response.task) {
                    showNotification(response.message || 'Công việc đã được tạo.');
                    const newCardHtml = createTaskCardHtml(response.task); // response.task phải đủ thông tin
                    // Chèn card mới vào TRƯỚC nút "add-card-placeholder"
                    $entry.siblings('.add-card-placeholder').before(newCardHtml);
                    $entry.remove(); 
                    // $columnContent.find('.add-card-placeholder').show(); // Đã bị ẩn, không cần show lại
                } else {
                    showNotification(response.message || 'Không thể tạo công việc.', 'error');
                }
            },
            error: function (jqXHR) {
                let errorMsg = 'Lỗi khi lưu công việc.';
                if (jqXHR.responseJSON) {
                    if (jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.title) {
                        errorMsg = jqXHR.responseJSON.errors.title[0];
                    } else if (jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    }
                } else {
                    errorMsg = `Lỗi máy chủ: ${jqXHR.statusText} (${jqXHR.status})`;
                }
                showNotification(errorMsg, 'error');
            },
            complete: function () {
                // Chỉ kích hoạt lại nút nếu nó vẫn còn trên DOM (trường hợp lỗi)
                if ($button.closest('body').length) {
                    $button.prop('disabled', false).text('Lưu');
                }
                // Hiển thị lại nút "Thêm công việc" placeholder nếu nó đã bị ẩn
                const $placeholder = $columnContent.find('.add-card-placeholder');
                if ($placeholder.is(':hidden')) {
                    $placeholder.show();
                }
            }
        });
    });

    $('#kanbanBoard').on('keypress', '.card-input', function (e) {
        if (e.which == 13 && !e.shiftKey) {
            e.preventDefault();
            $(this).closest('.new-card-entry').find('.save-card-btn').click();
        }
    });


    // --- Task Modal ---
    // Hàm đổ dữ liệu vào Modal
    function populateTaskModal(taskData) {
        currentOpenTaskId = taskData.id;
        originalTaskData = { // Lưu bản sao để so sánh khi sửa
            title: taskData.title,
            description: taskData.description,
            // Lưu thêm các trường khác nếu cần so sánh
        };

        $('#modalTaskId').val(taskData.id);
        $('#modalTaskTitleHeader').text(taskData.title); // Tiêu đề ở header modal
        $('#modalTaskTitleInput').val(taskData.title);   // Tiêu đề trong input
        $('#modalTaskColumnName').text(taskData.column_name || 'N/A'); // Tên cột

        // Xử lý Mô tả
        const $descDisplay = $('#modalTaskDescriptionContainer .description-box-display');
        const $descEdit = $('#modalTaskDescriptionContainer .description-box-edit');
        const $descTextarea = $('#modalTaskDescriptionTextarea');

        if (taskData.description) {
            $descDisplay.html(taskData.description.replace(/\n/g, '<br>')); // Hiển thị xuống dòng
        } else {
            $descDisplay.html('<em class="text-muted">Thêm mô tả chi tiết hơn...</em>');
        }
        $descTextarea.val(taskData.description || '');
        $descEdit.hide();
        $descDisplay.show();

        // Xử lý Người tham gia (Assignees)
        const $assigneesContainer = $('#modalTaskAssignees');
        $assigneesContainer.empty();
        if (taskData.assignees && taskData.assignees.length > 0) {
            taskData.assignees.forEach(assignee => {
                $assigneesContainer.append(
                    `<img src="https://i.pravatar.cc/30?u=${encodeURIComponent(assignee.email)}"
                         class="rounded-circle border border-white mr-n2" {{-- mr-n2 để chồng ảnh --}}
                         width="30" height="30"
                         title="${assignee.name}"
                         alt="${assignee.name}">`
                );
            });
        } else {
            $assigneesContainer.html('<span class="text-muted small">Chưa có ai tham gia.</span>');
        }

        // Xử lý Ngày hết hạn
        $('#modalDueDateBadge').text(taskData.formatted_due_date || 'Chưa đặt');
        if (taskData.due_date) {
            $('#modalDueDateBadge').removeClass('badge-light').addClass('badge-info'); // Hoặc warning/danger tùy logic
        } else {
            $('#modalDueDateBadge').removeClass('badge-info badge-warning badge-danger').addClass('badge-light');
        }

        // Xử lý Hoạt động và Bình luận
        const $activityLog = $('#modalTaskActivityLog');
        $activityLog.empty();
        let hasActivity = false;

        if (taskData.task_histories && taskData.task_histories.length > 0) {
            hasActivity = true;
            taskData.task_histories.forEach(history => {
                $activityLog.append(`
                    <div class="activity-item mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-start">
                            <img src="${history.user_avatar}" class="rounded-circle mr-2" width="32" height="32" alt="${history.user_name}">
                            <div>
                                <p class="mb-0"><strong>${history.user_name}</strong> ${history.action} <span class="text-info">${history.note || ''}</span></p>
                                <small class="text-muted">${history.time_ago}</small>
                            </div>
                        </div>
                    </div>
                `);
            });
        }
        if (taskData.comments && taskData.comments.length > 0) {
            hasActivity = true;
            taskData.comments.forEach(comment => {
                $activityLog.append(`
                    <div class="comment-item mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-start">
                            <img src="${comment.user_avatar}" class="rounded-circle mr-2" width="32" height="32" alt="${comment.user_name}">
                            <div>
                                <p class="mb-0"><strong>${comment.user_name}</strong> đã bình luận:</p>
                                <div style="white-space: pre-wrap;">${comment.content.replace(/\n/g, '<br>')}</div>
                                <small class="text-muted">${comment.time_ago}</small>
                            </div>
                        </div>
                    </div>
                `);
            });
        }

        if (!hasActivity) {
            $activityLog.html('<p class="text-muted small">Chưa có hoạt động nào.</p>');
        }


        // Mở modal
        $('#taskDetailModal').modal('show');
    }

    // Sự kiện click vào card task để mở modal
    $('#kanbanBoard').on('click', '.kanban-card[data-task-id]:not(.new-card-entry)', function (event) {
        if (isDragging || $(this).hasClass('ui-sortable-helper')) {
            return; // Không làm gì nếu đang kéo thả
        }

        const taskId = $(this).data('task-id');
        if (!taskId) {
            showNotification("Không tìm thấy ID công việc.", "error");
            return;
        }

        // Gọi AJAX để lấy chi tiết task
        const url = getRoute('tasksShowBase', { taskIdPlaceholder: taskId });
        if (url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route xem chi tiết task.', 'error');
            return;
        }

        // Hiển thị loading indicator (tùy chọn)
        // $('#taskDetailModal .modal-body').html('<p class="text-center">Đang tải...</p>');
        // $('#taskDetailModal').modal('show'); // Có thể show modal trước với loading

        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                if (response.success && response.task) {
                    populateTaskModal(response.task);
                } else {
                    showNotification(response.message || 'Không thể tải dữ liệu công việc.', 'error');
                    $('#taskDetailModal').modal('hide'); // Ẩn modal nếu lỗi
                }
            },
            error: function (jqXHR) {
                showNotification('Lỗi tải chi tiết công việc: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                $('#taskDetailModal').modal('hide'); // Ẩn modal nếu lỗi
            }
        });
    });

    // --- Xử lý các hành động trong Modal ---

    // Sửa Tiêu đề Task trong Modal
    $('#modalTaskTitleInput').on('blur', function () {
        const newTitle = $(this).val().trim();
        const taskId = $('#modalTaskId').val(); // Lấy taskId từ input ẩn trong modal

        if (!newTitle || !taskId || newTitle === originalTaskData.title) {
            $(this).val(originalTaskData.title); // Reset nếu rỗng, không có ID hoặc không đổi
            return;
        }

        $.ajax({
            url: getRoute('tasksUpdateBase', { taskIdPlaceholder: taskId }),
            method: 'PUT',
            data: { title: newTitle, _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success && response.task) {
                    $('#modalTaskTitleHeader').text(response.task.title);
                    originalTaskData.title = response.task.title; // Cập nhật dữ liệu gốc
                    // Cập nhật title trên card ở bảng Kanban
                    $(`.kanban-card[data-task-id="${taskId}"] .task-title`).text(response.task.title);
                    showNotification('Tiêu đề đã được cập nhật.', 'success');
                } else {
                    $('#modalTaskTitleInput').val(originalTaskData.title); // Hoàn tác
                    showNotification(response.message || 'Lỗi cập nhật tiêu đề.', 'error');
                }
            },
            error: function (jqXHR) {
                $('#modalTaskTitleInput').val(originalTaskData.title); // Hoàn tác
                showNotification('Lỗi AJAX khi cập nhật tiêu đề: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            }
        });
    }).on('keypress', function (e) {
        if (e.which === 13) { // Enter
            $(this).blur(); // Trigger sự kiện blur để lưu
        }
    });

    // Sửa Mô tả Task trong Modal
    $('#modalTaskDescriptionContainer').on('click', '.description-box-display', function () {
        $(this).hide();
        $('#modalTaskDescriptionContainer .description-box-edit').show().find('textarea').focus();
    });

    $('#modalTaskDescriptionContainer').on('click', '.cancel-description-btn', function () {
        $('#modalTaskDescriptionTextarea').val(originalTaskData.description || '');
        $('#modalTaskDescriptionContainer .description-box-edit').hide();
        $('#modalTaskDescriptionContainer .description-box-display').show();
    });

    $('#modalTaskDescriptionContainer').on('click', '.save-description-btn', function () {
        const newDescription = $('#modalTaskDescriptionTextarea').val(); // Không cần trim() với textarea
        const taskId = $('#modalTaskId').val();
        const $button = $(this);

        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Đang lưu...');

        $.ajax({
            url: getRoute('tasksUpdateBase', { taskIdPlaceholder: taskId }),
            method: 'PUT',
            data: { description: newDescription, _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success && response.task) {
                    const displayDesc = response.task.description ? response.task.description.replace(/\n/g, '<br>') : '<em class="text-muted">Thêm mô tả chi tiết hơn...</em>';
                    $('#modalTaskDescriptionContainer .description-box-display').html(displayDesc);
                    originalTaskData.description = response.task.description;
                    // Cập nhật preview trên card ở bảng Kanban (nếu có)
                    const $cardDescPreview = $(`.kanban-card[data-task-id="${taskId}"] .task-description-preview`);
                    if ($cardDescPreview.length) {
                        $cardDescPreview.text(response.task.description ? response.task.description.substring(0, 50) + '...' : '');
                    }
                    showNotification('Mô tả đã được cập nhật.', 'success');
                } else {
                    showNotification(response.message || 'Lỗi cập nhật mô tả.', 'error');
                }
            },
            error: function (jqXHR) {
                showNotification('Lỗi AJAX khi cập nhật mô tả: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            },
            complete: function () {
                $button.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Lưu');
                $('#modalTaskDescriptionContainer .description-box-edit').hide();
                $('#modalTaskDescriptionContainer .description-box-display').show();
            }
        });
    });

    // Xóa Task từ Modal
    $('#taskDetailModal').on('click', '#modalDeleteTaskTrigger', function () { // Gắn event vào modal để đảm bảo tồn tại
        const taskId = $('#modalTaskId').val();
        if (!taskId) {
            showNotification('Không tìm thấy ID công việc để xóa.', 'error');
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn xóa công việc "${originalTaskData.title || 'này'}" không? Hành động này không thể hoàn tác.`)) {
            return;
        }

        const $button = $(this);
        $button.addClass('disabled').html('<i class="fas fa-spinner fa-spin fa-fw mr-2"></i>Đang xóa...');

        $.ajax({
            url: getRoute('tasksDestroyBase', { taskIdPlaceholder: taskId }),
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    showNotification(response.message || 'Công việc đã được xóa.', 'success');
                    $('#taskDetailModal').modal('hide');
                    // Xóa card khỏi bảng Kanban
                    $(`.kanban-card[data-task-id="${taskId}"]`).fadeOut(300, function () { $(this).remove(); });
                } else {
                    showNotification(response.message || 'Không thể xóa công việc.', 'error');
                }
            },
            error: function (jqXHR) {
                showNotification('Lỗi AJAX khi xóa công việc: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            },
            complete: function () {
                $button.removeClass('disabled').html('<i class="fas fa-trash-alt fa-fw mr-2"></i>Xóa công việc');
            }
        });
    });


    // Reset modal khi đóng để không bị lưu dữ liệu cũ cho lần mở sau
    $('#taskDetailModal').on('hidden.bs.modal', function () {
        currentOpenTaskId = null;
        originalTaskData = {};
        $('#modalTaskId').val('');
        // Reset các input và text trong modal về trạng thái ban đầu
        $('#modalTaskTitleHeader').text('Chi tiết công việc');
        $('#modalTaskTitleInput').val('');
        $('#modalTaskColumnName').text('Tên Cột');
        $('#modalTaskDescriptionContainer .description-box-display').html('<em class="text-muted">Thêm mô tả chi tiết hơn...</em>');
        $('#modalTaskDescriptionTextarea').val('');
        $('#modalTaskDescriptionContainer .description-box-edit').hide();
        $('#modalTaskDescriptionContainer .description-box-display').show();
        $('#modalTaskAssignees').html('<span class="text-muted small">Chưa có ai tham gia.</span>');
        $('#modalDueDateBadge').text('Chưa đặt').removeClass('badge-info badge-warning badge-danger').addClass('badge-light');
        $('#modalTaskActivityLog').html('<p class="text-muted small">Lịch sử hoạt động và bình luận sẽ hiển thị ở đây.</p>');
        $('#modalNewCommentTextarea').val('');
    });


    // --- Public methods của TaskJS ---
    return {
        // ... (initializeSortableForColumn và initializeSortableForExistingColumns như cũ) ...
        initializeSortableForColumn: function ($columnContentElement) {
            if ($columnContentElement && $columnContentElement.length) {
                $columnContentElement.sortable({
                    connectWith: ".column-content",
                    items: "> .kanban-card[data-task-id]:not(.add-card-placeholder):not(.new-card-entry)",
                    placeholder: "kanban-placeholder",
                    forcePlaceholderSize: true,
                    tolerance: "pointer",
                    start: function (event, ui) {
                        isDragging = true;
                        ui.item.addClass('dragging');
                        ui.placeholder.height(ui.item.outerHeight());
                    },
                    stop: function (event, ui) {
                        setTimeout(function () { isDragging = false; }, 50);

                        ui.item.removeClass('dragging');
                        const $currentColumnContent = ui.item.closest('.column-content'); // Cột mà task được thả vào
                        const $addCardPlaceholder = $currentColumnContent.find('.add-card-placeholder');
                        $currentColumnContent.append($addCardPlaceholder); // Đảm bảo nút "Thêm" luôn ở cuối cột hiện tại

                        let taskId = ui.item.data('task-id');
                        let newColumnId = $currentColumnContent.data('column-id');
                        // Lấy thứ tự task CHỈ TRONG CỘT MỚI
                        let taskOrderInNewColumn = $currentColumnContent.children('.kanban-card[data-task-id]:not(.add-card-placeholder):not(.new-card-entry)').map(function () {
                            return $(this).data('task-id');
                        }).get();

                        const url = getRoute('tasksUpdatePosition');
                        if (url.startsWith('#ROUTE_')) {
                            showNotification('Lỗi cấu hình route cập nhật vị trí task.', 'error');
                            $(".column-content").sortable("cancel"); // Hủy thao tác kéo thả
                            return;
                        }
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                task_id: taskId,
                                new_column_id: newColumnId,
                                order: taskOrderInNewColumn,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (r) {
                                if (!r.success) {
                                    showNotification(r.message || 'Lỗi cập nhật vị trí.', 'error');
                                    $(".column-content").sortable("cancel");
                                } else {
                                    // showNotification(r.message || 'Vị trí đã được cập nhật.'); // Có thể bỏ qua nếu không muốn quá nhiều thông báo
                                }
                            },
                            error: function (e) {
                                showNotification('Lỗi AJAX cập nhật vị trí.', 'error');
                                $(".column-content").sortable("cancel");
                            }
                        });
                    }
                }).disableSelection();
            }
        },
        init: function () {
            this.initializeSortableForExistingColumns();
            console.log("TaskJS initialized. Click on task card will open modal.");
        },
        initializeSortableForExistingColumns: function () {
            const self = this;
            $('.column-content').each(function () {
                self.initializeSortableForColumn($(this));
            });
        }
    };
})(jQuery);

// --- Initializations for TaskJS ---
$(function () {
    if (typeof TaskJS !== 'undefined' && TaskJS.init) {
        TaskJS.init();
    } else {
        console.error("TaskJS is not defined or init method is missing.");
    }
});