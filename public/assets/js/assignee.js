var AssigneeManager = (function ($) {
    let currentTaskId = null;
    let currentBoardId = null;
    let currentAssignee = null; 
    let $assigneeSelect = null; 

    function showNotification(message, type = 'success') {
        alert(message);
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
        return url;
    }

    function createAssigneeDropdown() {
        if (!$assigneeSelect) {
            $assigneeSelect = $('<select class="form-control form-control-sm mt-2" id="taskAssigneeSelect" style="display:none;"></select>');
            $('#modalAssignMembersTrigger').after($assigneeSelect);

            $assigneeSelect.on('change', function () {
                const selectedUserId = $(this).val();
                handleAssigneeSelection(selectedUserId);
            });
        }
    }

    function populateAndShowDropdown(boardUsers) {
        if (!$assigneeSelect) createAssigneeDropdown();

        $assigneeSelect.empty().append('<option value="">-- Bỏ chọn người phụ trách --</option>');
        boardUsers.forEach(user => {
            $assigneeSelect.append(`<option value="${user.id}">${user.name} (${user.email})</option>`);
        });

        if (currentAssignee && currentAssignee.id) {
            $assigneeSelect.val(currentAssignee.id);
        } else {
            $assigneeSelect.val("");
        }
        $assigneeSelect.show();
        $('#modalAssignMembersTrigger').hide(); 
    }

    function handleAssigneeSelection(selectedUserId) {
        if (!currentTaskId) {
            showNotification('Lỗi: Không xác định được ID công việc.', 'error');
            return;
        }

        const previousAssigneeId = currentAssignee ? currentAssignee.id : null;

        if (selectedUserId == previousAssigneeId || (selectedUserId === "" && previousAssigneeId === null)) {
             $assigneeSelect.hide();
             $('#modalAssignMembersTrigger').show();
            return;
        }

        let ajaxOptions = {
            url: '',
            method: '',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function() {
                $assigneeSelect.prop('disabled', true);
            },
            complete: function() {
                $assigneeSelect.prop('disabled', false).hide();
                $('#modalAssignMembersTrigger').show();
            }
        };

        if (selectedUserId && selectedUserId !== "") { 
            ajaxOptions.data.user_id = selectedUserId; 
            if (previousAssigneeId) { 
                ajaxOptions.url = getRoute('assigneesUpdateBase', { taskIdPlaceholder: currentTaskId, userIdPlaceholder: selectedUserId }); 
                ajaxOptions.method = 'PUT';
            } else {
                ajaxOptions.url = getRoute('assigneesStoreBase', { taskIdPlaceholder: currentTaskId });
                ajaxOptions.method = 'POST';
            }
        } else { 
            if (previousAssigneeId) {
                ajaxOptions.url = getRoute('assigneesDestroyBase', { taskIdPlaceholder: currentTaskId, userIdPlaceholder: previousAssigneeId }); // userIdPlaceholder is the one to remove
                ajaxOptions.method = 'DELETE';
            } else {
                $assigneeSelect.hide();
                $('#modalAssignMembersTrigger').show();
                return;
            }
        }

        if (ajaxOptions.url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route cho người phụ trách.', 'error');
            $assigneeSelect.hide();
            $('#modalAssignMembersTrigger').show();
            return;
        }
        
        $.ajax(ajaxOptions)
            .done(function (response) {
                if (response.success) {
                    showNotification(response.message || 'Cập nhật người phụ trách thành công.', 'success');

                    if (response.task && response.task.assignees) {
                         updateAssigneeVisuals(response.task.assignees);
                         // Update currentAssignee based on the response
                         currentAssignee = response.task.assignees.length > 0 ? response.task.assignees[0] : null;
                    } else if (selectedUserId && selectedUserId !== "") {
                         if (response.assignee) {
                            updateAssigneeVisuals([response.assignee]);
                            currentAssignee = response.assignee;
                         } else { 

                            updateAssigneeVisuals([]); 
                            currentAssignee = { id: selectedUserId, name: 'N/A', avatar_url: '' }; 
                            console.warn("Assignee details (name, avatar) not available after update. Visuals may be incomplete.");
                         }
                    } else { 
                        updateAssigneeVisuals([]);
                        currentAssignee = null;
                    }
                } else {
                    showNotification(response.message || 'Lỗi cập nhật người phụ trách.', 'error');
                }
            })
            .fail(function (jqXHR) {
                showNotification('Lỗi máy chủ: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            });
    }

    function updateAssigneeVisuals(assignees) {
        const $assigneesContainer = $('#modalTaskAssignees');
        $assigneesContainer.empty();

        if (assignees && assignees.length > 0) {
            const assignee = assignees[0];
            const assigneeName = assignee.name || 'Không rõ tên';
            const assigneeAvatar = assignee.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(assigneeName)}&background=random&size=30`;

            $assigneesContainer.append(`
                <img src="${assigneeAvatar}"
                     class="rounded-circle border border-white" 
                     width="30" height="30"
                     title="${assigneeName} (ID: ${assignee.id})"
                     alt="${assigneeName}">
                <span class="ml-2 align-self-center">${assigneeName}</span>
            `);
        } else {
            $assigneesContainer.html('<span class="text-muted small">Chưa có ai tham gia.</span>');
        }
    }


    return {
        init: function () {
            currentBoardId = window.currentBoardId; 
            createAssigneeDropdown(); 

            $(document).on('click', '#modalAssignMembersTrigger', function (e) {
                e.preventDefault();
                if (!currentTaskId) {
                    showNotification('Vui lòng mở một công việc để gán người phụ trách.', 'info');
                    return;
                }
                if (!currentBoardId) {
                    showNotification('Lỗi: Không xác định được ID bảng.', 'error');
                    return;
                }

                const $trigger = $(this);
                $trigger.prop('disabled', true).find('i').removeClass('fa-user').addClass('fa-spinner fa-spin');

                const url = getRoute('boardsAssignedUsersBase', { boardIdPlaceholder: currentBoardId });
                if (url.startsWith('#ROUTE_')) {
                    showNotification('Lỗi cấu hình route lấy danh sách người dùng.', 'error');
                    $trigger.prop('disabled', false).find('i').removeClass('fa-spinner fa-spin').addClass('fa-user');
                    return;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (response) {
                        if (response.success && response.users) {
                            populateAndShowDropdown(response.users);
                        } else {
                            showNotification(response.message || 'Không thể tải danh sách người dùng.', '');
                        }
                    },
                    error: function (jqXHR) {
                        showNotification('Lỗi tải danh sách người dùng: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                    },
                    complete: function() {
                        $trigger.prop('disabled', false).find('i').removeClass('fa-spinner fa-spin').addClass('fa-user');
                    }
                });
            });

            $('#taskDetailModal').on('hidden.bs.modal', function () {
                if ($assigneeSelect) {
                    $assigneeSelect.hide().empty();
                }
                $('#modalAssignMembersTrigger').show();
                currentTaskId = null;
                currentAssignee = null;
            });
        },

        setInitialTaskData: function (taskId, taskAssignees) { 
            currentTaskId = taskId;
            if (taskAssignees && taskAssignees.length > 0) {
                currentAssignee = taskAssignees[0];
            } else {
                currentAssignee = null;
            }

            if ($assigneeSelect) {
                $assigneeSelect.hide();
                $('#modalAssignMembersTrigger').show();
            }
        },

        refreshAssigneeDisplay: function(assignees) {
            updateAssigneeVisuals(assignees);
            currentAssignee = (assignees && assignees.length > 0) ? assignees[0] : null;
        }
    };
})(jQuery);


$(function () {
    if (typeof AssigneeManager !== 'undefined' && AssigneeManager.init) {
        AssigneeManager.init();
        console.log("AssigneeManager initialized.");
    } else {
        console.error("AssigneeManager is not defined or init method is missing.");
    }
});