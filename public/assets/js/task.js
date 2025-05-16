var TaskJS = (function ($) {
    let isDragging = false;
    let currentOpenTaskId = null;
    let originalTaskData = {};
    function showNotification(message, type = 'success') {
        alert(message);
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
            // console.warn(`Potential unresolved placeholder in URL for route "${routeName}": ${url}`);
        }
        return url;
    }



    // --- thêm task---
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

    // --- Task Modal ---
    // Hàm đổ dữ liệu vào Modal
    function populateTaskModal(taskData) {
        currentOpenTaskId = taskData.id;
        originalTaskData = { // Lưu bản sao để so sánh khi sửa
            title: taskData.title,
            description: taskData.description,
            // Lưu thêm các trường khác nếu cần so sánh
        };
        let columnColor = 'black';

        switch (taskData.column_name) {
            case 'Đang làm':
                columnColor = 'orange';
                break;
            case 'Hoàn thành':
                columnColor = 'green';
                break;
            case 'Việc cần làm':
                columnColor = 'red';
                break;
            default:
                columnColor = 'gray';
        }

        $('#modalTaskId').val(taskData.id);
        $('#modalTaskTitleHeader').text(taskData.title);
        $('#modalTaskTitleInput').val(taskData.title);
        $('#modalTaskColumnName')
            .text(taskData.column_name || 'Không xác định')
            .css('color', columnColor);

        // Xử lý Mô tả
        const $descDisplay = $('#modalTaskDescriptionContainer .description-box-display');
        const $descEdit = $('#modalTaskDescriptionContainer .description-box-edit');
        const $descTextarea = $('#modalTaskDescriptionTextarea');

        if (taskData.description) {
            $descDisplay.html(taskData.description.replace(/\n/g, '<br>'));
        } else {
            $descDisplay.html('<em class="text-muted">Thêm mô tả chi tiết hơn...</em>');
        }
        $descTextarea.val(taskData.description || '');
        $descEdit.hide();
        $descDisplay.show();

        // Xử lý assignees
        const $assigneesContainer = $('#modalTaskAssignees');
        $assigneesContainer.empty();

        if (taskData.assignees && taskData.assignees.length > 0) {
            taskData.assignees.forEach(assignee => {
                const assigneeName = assignee.name || 'Không rõ tên';
                const assigneeAvatar = assignee.avatar_url || 'https://i.pravatar.cc/30?u=' + encodeURIComponent(assignee.email || `unknown_${Date.now()}`);

                $assigneesContainer.append(`
            <img src="${assigneeAvatar}"
                 class="rounded-circle border border-white mr-n2" 
                 width="30" height="30"
                 title="${assigneeName}"
                 alt="${assigneeName}">
            <span class="ml-2 align-self-center">${assigneeName}</span>
        `);
            });
        } else {
            $assigneesContainer.html('<span class="text-muted small">Chưa có ai tham gia.</span>');
        }

        if (typeof AssigneeManager !== 'undefined' && AssigneeManager.setInitialTaskData) {
            AssigneeManager.setInitialTaskData(taskData.id, taskData.assignees);
        }
        // Xử lý Ngày hết hạn
        $('#modalDueDateBadge').text(taskData.formatted_due_date || 'Chưa đặt');
        if (taskData.due_date) {
            $('#modalDueDateBadge').removeClass('badge-light').addClass('badge-info');
        } else {
            $('#modalDueDateBadge').removeClass('badge-info badge-warning badge-danger').addClass('badge-light');
        }

        // Xử lý Hoạt động 
        const $activityLog = $('#modalTaskActivityLog');
        const $loadMoreBtn = $('#loadMoreActivity');
        let currentPage = 0;
        const perPage = 5;
        let sortedHistories = [];
        $activityLog.empty();

        function renderActivityLogPage(page) {
            const start = page * perPage;
            const end = start + perPage;
            const pageData = sortedHistories.slice(start, end);

            pageData.forEach(history => {
                const formatHistoryCreatedAt = history.created_at
                    ? dayjs(history.created_at).format("DD/MM/YYYY HH:mm")
                    : "Không xác định";

                $activityLog.append(`
            <div class="activity-item mb-2 pb-2 border-bottom">
                <div class="d-flex align-items-start">
                    <img src="${history.user_avatar || 'https://i.pravatar.cc/150?img=3'}"
                         class="rounded-circle mr-2"
                         width="32"
                         height="32"
                         alt="${history.user_name}">
                    <div>
                        <p class="mb-0">
                            <span class="font-weight-bold">${history.user_name || 'Hệ thống'}</span>
                            ${history.note || ''}
                        </p>
                        <small class="text-muted">${formatHistoryCreatedAt}</small>
                    </div>
                </div>
            </div>
        `);
            });

            if (end >= sortedHistories.length || pageData.length < perPage) {
                $loadMoreBtn.hide();
            }
        }

        if (taskData.task_histories && taskData.task_histories.length > 0) {
            currentPage = 0;
            sortedHistories = taskData.task_histories.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            $activityLog.empty();
            renderActivityLogPage(currentPage);
            if (sortedHistories.length > perPage) {
                $loadMoreBtn.show();
            } else {
                $loadMoreBtn.hide();
            }
        } else {
            $activityLog.html('<p class="text-muted small">Chưa có hoạt động nào.</p>');
            $loadMoreBtn.hide();
        }


        $loadMoreBtn.on('click', function () {
            currentPage++;
            renderActivityLogPage(currentPage);
        });


        // Riêng cho comments, kiểm tra nếu không có comment thì hiển thị thông báo trong #modalDisplayComment
        const $commentLog = $('#modalDisplayComment');
        let hasComments = false;
        $commentLog.empty();
        if (taskData.comments && taskData.comments.length > 0) {
            hasComments = true;
            taskData.comments.forEach(comment => {
                $commentLog.append(`
                   <div class="media mb-3" id="comment-${comment.id}">
                        <img src="${comment.user_avatar}" class="rounded-circle mr-2" width="32" height="32" alt="${comment.user_name}">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">${comment.user_name} <small class="text-muted">(${comment.time_ago})</small></h6>
                            <p>${comment.content.replace(/\n/g, '<br>')}</p>
                        </div>
                        <div class="dropdown mr-3">
                                <a href="#" class="text-muted dropdown-toggle-no-caret" id="commentMenu${comment.id}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Tùy chọn bình luận">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="commentMenu${comment.id}">
                                    <a class="dropdown-item delete-comment-btn text-danger" href="#" data-comment-id="${comment.id}"><i class="fas fa-trash-alt fa-fw mr-2"></i>Xoá</a>
                                </div>
                        </div>
                    </div>
                `);
            });
        }

        // === Xử lý Đính kèm (Attachments) ===
        if (typeof AttachmentManager !== 'undefined' && AttachmentManager.loadAttachments) {
            AttachmentManager.loadAttachments(taskData.id);
        } else {
            $('#modalTaskAttachments').html('<p class="text-danger small">Lỗi: Mô-đun đính kèm chưa được tải.</p>');
            console.error("TaskJS: AttachmentManager or loadAttachments method is missing.");
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

        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                if (response.success && response.task) {
                    populateTaskModal(response.task);
                } else {
                    showNotification(response.message || 'Không thể tải dữ liệu công việc.', 'error');
                    $('#taskDetailModal').modal('hide');
                }
            },
            error: function (jqXHR) {
                showNotification('Lỗi tải chi tiết công việc: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                $('#taskDetailModal').modal('hide');
            }
        });
    });

    // --- Xử lý các hành động trong Modal ---

    // Sửa Tiêu đề Task trong Modal
    $('#modalTaskTitleInput').on('blur', function () {
        const newTitle = $(this).val().trim();
        const taskId = $('#modalTaskId').val(); // Lấy taskId từ input ẩn trong modal

        if (!newTitle || !taskId || newTitle === originalTaskData.title) {
            $(this).val(originalTaskData.title);
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
                showNotification((jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            }
        });
    }).on('keypress', function (e) {
        if (e.which === 13) { // Enter
            $(this).blur(); // Trigger sự kiện blur để lưu
        }
    });

    // Sửa Mô tả Task trong Modal
    // Hiển thị form sửa mô tả
    $(document).on('click', '#modalTaskDescriptionContainer .description-box-display', function () {
        $(this).hide();
        $('#modalTaskDescriptionContainer .description-box-edit').show();
    });

    // Hủy sửa mô tả
    $(document).on('click', '.cancel-description-btn', function () {
        $('#modalTaskDescriptionTextarea').val(originalTaskData.description || '');
        $('#modalTaskDescriptionContainer .description-box-edit').hide();
        $('#modalTaskDescriptionContainer .description-box-display').show();
    });


    $(document).on('click', '#modalArchiveTaskTrigger', function () {
        const taskId = $('#modalTaskId').val();
        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Đang lưu...');
        const url = getRoute('tasksUpdateBase', { taskIdPlaceholder: taskId });

        if (!taskId) {
            showNotification('Không tìm thấy ID công việc.', 'error');
            return;
        }

        const title = $('#modalTaskTitleInput').val().trim();
        const description = $('#modalTaskDescriptionTextarea').val().trim();
        const newDueDate = $('#modalDueDateInput').val().trim();

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _method: 'PUT',
                title: title,
                description: description,
                due_date: newDueDate,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                if (res.success && res.task) {
                    const task = res.task;

                    // === MÔ TẢ ===
                    const displayDesc = task.description
                        ? task.description.replace(/\n/g, '<br>')
                        : '<em class="text-muted">Thêm mô tả chi tiết hơn...</em>';
                    $('#modalTaskDescriptionContainer .description-box-display').html(displayDesc);
                    originalTaskData.description = task.description;

                    // === NGÀY HẾT HẠN ===
                    const badge = task.due_date
                        ? dayjs(task.due_date).format('DD/MM/YYYY')
                        : 'Chưa đặt';
                    $('#modalDueDateBadge').text(badge);
                    originalTaskData.due_date = task.due_date;

                    // === CẬP NHẬT THẺ TRÊN KANBAN ===
                    const $card = $(`.kanban-card[data-task-id="${taskId}"]`);
                    $card.find('.task-due-date').remove();
                    if (task.due_date) {
                        $card.append(`<small class="task-due-date text-warning d-block mt-1">⏰ ${badge}</small>`);
                    }
                    $card.find('h5').text(title);
                    $card.find('.task-description-preview').text(task.description ? task.description.substring(0, 50) + '...' : '');

                    showNotification('Đã cập nhật mô tả, tiêu đề và ngày hết hạn.', 'success');
                } else {
                    showNotification(res.message || 'Lỗi cập nhật công việc.', 'error');
                }
            },
            error: function (xhr) {
                showNotification((xhr.responseJSON?.message || xhr.statusText), 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-archive fa-fw mr-2"></i>Lưu thay đổi');
                $('#modalTaskDescriptionContainer .description-box-edit').hide();
                $('#modalTaskDescriptionContainer .description-box-display').show();
                $('#dueDatePickerContainer').hide();
            }
        });
    });


    // Xóa Task từ Modal
    $(document).on('click', '#modalDeleteTaskTrigger', function () {
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
                showNotification((jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
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
        $('#modalDisplayComment').val('');
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
                        const $currentColumnContent = ui.item.closest('.column-content');
                        const $addCardPlaceholder = $currentColumnContent.find('.add-card-placeholder');
                        $currentColumnContent.append($addCardPlaceholder);

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
                                    showNotification(r.message || 'Cập nhật vị trí thất bại.', 'error');
                                    $(".column-content").sortable("cancel");
                                } else {

                                }
                            },
                            error: function (e) {
                                showNotification('Cập nhật vị trí thất bại.', 'error');
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

    $('#taskDetailModal').on('shown.bs.modal', function () {
        const $dialog = $(this).find('.modal-dialog');
        const modalWidth = 1140;
        const modalHeight = 900;
        $dialog.css('display', 'block');
        const winWidth = $(window).width();
        const winHeight = $(window).height();
        const top = Math.max((winHeight - modalHeight) / 2, 20);
        const left = Math.max((winWidth - modalWidth) / 2, 20);
        $dialog.css({
            position: 'absolute',
            width: modalWidth + 'px',
            height: modalHeight + 'px',
            top: top + 'px',
            left: left + 'px',
            margin: 0
        }).draggable({
            handle: ".modal-header"
        });
        $dialog.find('.modal-header').css('cursor', 'move');
    });



});