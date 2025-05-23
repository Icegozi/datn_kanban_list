$(function () {
    const boardId = $('#kanbanBoard').data('board-id'); 
    function showNotification(message, type = 'success') {
        alert(message);
    }

    function getRoute(routeName, params = {}) {
        let url = window.routeUrls[routeName] || '';
        for (const key in params) {
            url = url.replace(`:${key}Placeholder`, params[key]);
            url = url.replace(`:${key}`, params[key]);
        }

        if (url.includes(':boardId') && !params['boardId'] && window.currentBoardId) {
            url = url.replace(':boardId', window.currentBoardId);
        }
        if (url.includes(':boardIdPlaceholder') && !params['boardIdPlaceholder'] && window.currentBoardId) {
            url = url.replace(':boardIdPlaceholder', window.currentBoardId);
        }

        if (url.includes(':columnId') && !params['columnId']) console.warn("Column ID missing for route", routeName);
        console.log(url);

        return url;
    }

    // --- Initialize Sortables ---
    function initializeCardSortable() {
        $(".column-content").sortable({
            connectWith: ".column-content",
            items: "> .kanban-card:not(.add-card-placeholder)", 
            placeholder: "kanban-placeholder",
            forcePlaceholderSize: true,
            tolerance: "pointer",
            start: function (event, ui) {
                ui.item.addClass('dragging');
                ui.placeholder.height(ui.item.outerHeight());
            },
            stop: function (event, ui) {
                ui.item.removeClass('dragging');

                // --- Giữ thẻ "Thêm công việc" ở cuối ---
                const column = ui.item.closest('.column-content');
                const addCard = column.find('.add-card-placeholder');
                column.append(addCard); 

                // --- Gửi dữ liệu mới về server ---
                let taskId = ui.item.data(' task-id');
                let newColumnId = ui.item.closest('.kanban-column').data('column-id');
                let taskOrder = ui.item.parent().children('.kanban-card').not('.add-card-placeholder').map(function () {
                    return $(this).data('task-id');
                }).get();

            }
        }).disableSelection();
    }


    function initializeColumnSortable() {
        $("#kanbanBoard").sortable({
            // Chỉ cho phép sắp xếp các cột thực tế, không phải nút "Add column"
            items: "> .kanban-column:not(.add-column-trigger)",
            // Chỉ cho phép kéo thả bằng phần header của cột
            handle: ".column-header",
            // Class CSS cho placeholder (vị trí ảo khi kéo)
            placeholder: "kanban-column column-placeholder", // Cần định nghĩa style cho class này trong CSS
            forcePlaceholderSize: true,
            tolerance: "pointer",
            start: function (event, ui) {
                // Đảm bảo placeholder có kích thước giống cột thật
                ui.placeholder.height(ui.item.outerHeight());
                ui.placeholder.width(ui.item.outerWidth());
            },
            // --- QUAN TRỌNG: Gọi hàm updateColumnOrder khi dừng kéo thả ---
            stop: function (event, ui) {
                updateColumnOrder();
            }
        }).disableSelection(); 
    }

    // --- HÀM CẬP NHẬT THỨ TỰ CỘT ---
    function updateColumnOrder() {
        // Lấy danh sách các ID cột theo thứ tự hiện tại trên DOM
        let orderedColumnIds = $("#kanbanBoard .kanban-column:not(.add-column-trigger)")
            .map(function () {
                // Lấy giá trị từ data-column-id của mỗi cột
                return $(this).data("column-id");
            }).get(); // Chuyển thành mảng JavaScript

        console.log("New column order:", orderedColumnIds); // Để debug

        // Lấy URL cho route reorder
        const url = getRoute('columnsReorderBase', { boardId: boardId });
        console.log("Sending reorder request to:", url); // Để debug

        if (!url || !orderedColumnIds || orderedColumnIds.length === 0) {
            console.warn("Could not determine reorder URL or column order.");
            return; // Không làm gì nếu không có URL hoặc không có cột để sắp xếp
        }

        // Gửi yêu cầu AJAX đến server
        $.ajax({
            url: url,
            method: 'POST', // Phương thức POST như đã định nghĩa trong route
            data: {
                order: orderedColumnIds,
            },
            success: function (response) {
                if (response.success) {
                    // showNotification(response.message); // Thông báo thành công
                } else {
                    $("#kanbanBoard").sortable("cancel");
                }
            },
            error: function (jqXHR) {
                console.error("AJAX Error Details:", jqXHR);
                showNotification(`Error ${jqXHR.status}: ${jqXHR.statusText}. Check console.`, 'error');
                $("#kanbanBoard").sortable("cancel");
            }
        });
    }

    // --- Column Creation ---
    $('#addColumnBtn').on('click', function () {
        $(this).hide();
        $('.add-column-form').show();
        $('#newColumnName').focus();
    });

    $('#cancelNewColumnBtn').on('click', function () {
        $('.add-column-form').hide();
        $('#newColumnName').val('');
        $('#addColumnBtn').show();
    });

    $('#saveNewColumnBtn').on('click', function () {
        const columnName = $('#newColumnName').val().trim();
        if (!columnName) {
            showNotification('Vui lòng điền tên cột', 'error');
            $('#newColumnName').focus();
            return;
        }

        const url = getRoute('columnsStoreBase', { boardId: boardId });

        const $button = $(this);
        $button.prop('disabled', true).text('Đang xử lý...');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                name: columnName
            },
            success: function (response) {
                if (response.success && response.column) {
                    showNotification(response.message);
                    // Create new column HTML (Basic - enhance with actual structure)
                    const newColumnHtml = `
                    <div class="kanban-column" data-column-id="${response.column.id}">
                        <div class="column-header d-flex justify-content-between align-items-center mb-3">
                            <h5 class="column-title flex-grow-1 mr-2" data-column-id="${response.column.id}">${response.column.name}</h5>
                            <div class="column-actions">
                                <button class="btn btn-sm btn-light edit-column-btn" title="Đổi tên cột"><i class="fas fa-pencil-alt"></i></button>
                                <button class="btn btn-sm btn-light delete-column-btn" title="Xoá cột"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        <div class="column-content flex-grow-1" data-column-id="${response.column.id}">
                            <div class="add-card-placeholder mt-2">
                                <i class="fas fa-plus"></i>
                                <div>Thêm công việc</div>
                            </div>
                        </div>
                    </div>`;
                    // Insert before the 'Add Column' trigger
                    $('.add-column-trigger').before(newColumnHtml);
                    $('#cancelNewColumnBtn').click();
                    initializeCardSortable();
                    $("#kanbanBoard").sortable("refresh");
                } else {
                    showNotification(response.message || 'Failed to create column.', 'error');
                }
            },
            error: function (jqXHR) {
                let errorMsg = 'Error saving column.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.name) {
                    errorMsg = jqXHR.responseJSON.errors.name[0]; // Show validation error
                } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                showNotification(errorMsg, 'error');
            },
            complete: function () {
                $button.prop('disabled', false).text('Lưu');
            }
        });
    });

    // Allow Enter key to save new column
    $('#newColumnName').on('keypress', function (e) {
        if (e.which == 13) {
            e.preventDefault();
            $('#saveNewColumnBtn').click();
        }
    });


    // --- Column Renaming ---
    $('#kanbanBoard').on('click', '.edit-column-btn', function () {
        const $header = $(this).closest('.column-header');
        const $title = $header.find('.column-title');
        const currentName = $title.text();
        const columnId = $title.data('column-id');

        // Replace title with input field
        const $input = $('<input type="text" class="form-control form-control-sm column-title-input">')
            .val(currentName)
            .data('original-name', currentName)
            .data('column-id', columnId);

        $title.hide().after($input);
        $input.focus().select();
    });

    $('#kanbanBoard').on('blur keypress', '.column-title-input', function (e) {
        if (e.type === 'keypress' && e.which !== 13) {
            return;
        }
        e.preventDefault();

        const $input = $(this);
        const newName = $input.val().trim();
        const originalName = $input.data('original-name');
        const columnId = $input.data('column-id');
        const $title = $input.prev('.column-title');

        if (newName === originalName || !newName) {
            $input.remove();
            $title.show();
            if (!newName) showNotification('Column name cannot be empty.', 'warning');
            return;
        }

        // AJAX Call to update
        const url = getRoute('columnsUpdateBase', { boardId: boardId, columnId: columnId });

        $.ajax({
            url: url,
            method: 'PUT', // Or PATCH
            data: { name: newName },
            success: function (response) {
                if (response.success) {
                    $title.text(response.new_name);
                    showNotification(response.message);
                } else {
                    showNotification(response.message || 'Failed to update name.', 'error');
                    $title.text(originalName);
                }
            },
            error: function (jqXHR) {
                let errorMsg = 'Error updating column name.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.name) {
                    errorMsg = jqXHR.responseJSON.errors.name[0];
                } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                showNotification(errorMsg, 'error');
                $title.text(originalName);
            },
            complete: function () {
                $input.remove();
                $title.show();
            }
        });
    });

    // --- Column Deletion ---
    $('#kanbanBoard').on('click', '.delete-column-btn', function () {
        const $button = $(this);
        const $column = $button.closest('.kanban-column');
        const columnId = $column.data('column-id');
        const columnName = $column.find('.column-title').text();

        // --- Confirmation ---
        // Simple confirmation for now. Enhance with task check later.
        if (!confirm(`Bạn có chắc muốn xoá cột "${columnName}"?\n\nCẢNH BÁO: Mọi công việc trong cột này cũng sẽ bị xoá!`)) {
            return;
        }

        const url = getRoute('columnsDestroyBase', { boardId: boardId, columnId: columnId });

        $button.prop('disabled', true); 

        $.ajax({
            url: url,
            method: 'DELETE',
            success: function (response) {
                if (response.success) {
                    showNotification(response.message);
                    $column.fadeOut(300, function () {
                        $(this).remove();
                        $("#kanbanBoard").sortable("refresh");
                    });
                } else {
                    showNotification(response.message || 'Failed to delete column.', 'error');
                    $button.prop('disabled', false); 
                }
            },
            error: function (jqXHR) {
                showNotification((jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                $button.prop('disabled', false); 
            }
        });
    });

    $('#kanbanBoard').on('click', '.cancel-card-btn', function () {
        const $entry = $(this).closest('.new-card-entry');
        const $placeholder = $entry.siblings('.add-card-placeholder');
        $entry.remove();
        $placeholder.show();
    });

    // Save new card 
    $('#kanbanBoard').on('click', '.save-card-btn', function () {
        const $entry = $(this).closest('.new-card-entry');
        const $input = $entry.find('.card-input');
        const cardTitle = $input.val().trim();
        const $columnContent = $entry.closest('.column-content');
        const columnId = $columnContent.data('column-id');

        if (cardTitle) {
            const url = getRoute('tasksStoreBase', { columnId: columnId });
            if (url.startsWith('#ROUTE_')) {
                showNotification('Lỗi cấu hình route tạo task.', 'error');
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    title: cardTitle,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log(response.task.id);
                    
                    if (response.success && response.task) {
                        const savedCardHtml = `<div class="kanban-card" data-task-id="${response.task.id}"><h5>${cardTitle}</h5></div>`;
                        $entry.replaceWith(savedCardHtml);
                        $columnContent.find('.add-card-placeholder').show();
                        initializeCardSortable(); 
                        location.reload();
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
            });
        } else {
            showNotification('Card title cannot be empty.', 'warning');
            $input.focus();
        }
    });
    $('#kanbanBoard').on('keypress', '.card-input', function (e) {
        if (e.which == 13 && !e.shiftKey) {
            e.preventDefault();
            $(this).closest('.new-card-entry').find('.save-card-btn').click();
        }
    });


    // --- Initializations ---
    initializeCardSortable();
    initializeColumnSortable();

}); 