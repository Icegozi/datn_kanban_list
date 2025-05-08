// D:\Đồ án tốt nghiệp\projects\datn_kanban_list\public\assets\js\task.js
// Tạo một namespace để tránh xung đột và dễ gọi từ column.js
var TaskJS = (function ($) {
    let isDragging = false;
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

    // --- Helper function to create task card HTML ---
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
        const columnId = $columnContent.data('column-id');

        if (!cardTitle) {
            showNotification('Tiêu đề công việc không được để trống.', 'warning');
            $input.focus();
            return;
        }

        const url = getRoute('tasksStoreBase', { columnId: columnId });
        if (url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route tạo task.', 'error');
            return;
        }

        $button.prop('disabled', true).text('Đang lưu...');

        $.ajax({
            url: url,
            method: 'POST',
            data: { title: cardTitle },
            success: function (response) {
                if (response.success && response.task) {
                    showNotification(response.message || 'Công việc đã được tạo.');
                    const newCardHtml = createTaskCardHtml(response.task);
                    $(newCardHtml).insertBefore($entry.siblings('.add-card-placeholder'));
                    $entry.remove();
                    $columnContent.find('.add-card-placeholder').show();
                } else {
                    showNotification(response.message || 'Không thể tạo công việc.', 'error');
                }
            },
            error: function (jqXHR) {
                let errorMsg = 'Lỗi khi lưu công việc.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.title) {
                    errorMsg = jqXHR.responseJSON.errors.title[0];
                } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                showNotification(errorMsg, 'error');
            },
            complete: function () {
                // Button is removed on success, so no need to re-enable if not preserved.
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
    // --- Task Modal Logic ---
    $('#kanbanBoard').on('click', '.kanban-card[data-task-id]:not(.new-card-entry)', function (event) {
        if (isDragging) {
            console.log("Drag detected, preventing navigation.");
            // isDragging sẽ được reset bởi stop của sortable, hoặc có thể reset ở đây nếu cần
            // setTimeout(function() { isDragging = false; }, 0);
            return;
        }

        const taskId = $(this).data('task-id');
        console.log("Task card clicked! Task ID:", taskId);

        if (!taskId) {
            console.error("Task ID is missing from the card.");
            showNotification("Không tìm thấy ID công việc.", "error");
            return;
        }

        // Lấy URL cho trang chi tiết task từ window.routeUrls
        // Giả sử bạn sẽ thêm 'tasksShowPageBase' vào window.routeUrls
        const detailPageUrl = getRoute('tasksShowPageBase', { taskId: taskId });
        console.log("Redirecting to task detail page:", detailPageUrl);

        if (detailPageUrl.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route trang chi tiết task.', 'error');
            return;
        }

        // Chuyển hướng đến trang chi tiết task
        window.location.href = detailPageUrl;
    });


    // --- Public methods ---
    return {
        initializeSortableForColumn: function($columnContentElement) {
            if ($columnContentElement && $columnContentElement.length) {
                $columnContentElement.sortable({
                    connectWith: ".column-content",
                    items: "> .kanban-card[data-task-id]:not(.add-card-placeholder):not(.new-card-entry)",
                    placeholder: "kanban-placeholder",
                    forcePlaceholderSize: true,
                    tolerance: "pointer",
                    start: function (event, ui) {
                        isDragging = true; // Đặt cờ khi bắt đầu kéo
                        ui.item.addClass('dragging');
                        ui.placeholder.height(ui.item.outerHeight());
                    },
                    stop: function (event, ui) {
                        setTimeout(function() { isDragging = false; }, 50); // Reset cờ sau một chút để sự kiện click không bị ảnh hưởng nếu có

                        ui.item.removeClass('dragging');
                        const $columnContent = ui.item.closest('.column-content');
                        const $addCardPlaceholder = $columnContent.find('.add-card-placeholder');
                        $columnContent.append($addCardPlaceholder);

                        let taskId = ui.item.data('task-id');
                        let newColumnId = $columnContent.data('column-id');
                        let taskOrderInNewColumn = $columnContent.children('.kanban-card[data-task-id]:not(.add-card-placeholder):not(.new-card-entry)').map(function () {
                            return $(this).data('task-id');
                        }).get();

                        const url = getRoute('tasksUpdatePosition');
                        if (url.startsWith('#ROUTE_')) {
                            showNotification('Lỗi cấu hình route cập nhật vị trí task.', 'error');
                            $(".column-content").sortable("cancel");
                            return;
                        }
                        $.ajax({
                            url: url, method: 'POST', data: { task_id: taskId, new_column_id: newColumnId, order: taskOrderInNewColumn },
                            success: function(r){ if(!r.success){ showNotification(r.message || 'Lỗi cập nhật vị trí.', 'error'); $(".column-content").sortable("cancel");}},
                            error: function(e){ showNotification('Lỗi AJAX cập nhật vị trí.', 'error'); $(".column-content").sortable("cancel");}
                        });
                    }
                }).disableSelection();
            }
        },
        init: function() {
            this.initializeSortableForExistingColumns();
            console.log("TaskJS initialized for board view. Click on task card will redirect.");
        },
        initializeSortableForExistingColumns: function() {
            const self = this;
            $('.column-content').each(function() {
                self.initializeSortableForColumn($(this));
            });
        }
    };

})(jQuery);

// --- Initializations for TaskJS ---
$(function () {
    TaskJS.init();
});