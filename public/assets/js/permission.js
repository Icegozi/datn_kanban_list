$(document).ready(function () {
    function buildUrl(template, replacements) {
        let url = template;
        for (const key in replacements) {
            url = url.replace(`:${key}`, replacements[key]);
        }
        return url;
    }

    const boardId = window.boardId;

    $('.member-role-select').on('change', function () {
        const newRolePermissionName = $(this).val();
        const memberId = $(this).closest('tr').data('member-id');
        const $select = $(this);

        if (!confirm(`Thay đổi vai trò của thành viên này thành "${$select.find('option:selected').text()}"?`)) {
            return;
        }

        const updateRoleUrl = buildUrl(window.routeUrls.boardsMembersUpdateRole, {
            boardIdPlaceholder: boardId,
            memberIdPlaceholder: memberId
        });

        $.ajax({
            url: updateRoleUrl,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                new_role_permission_name: newRolePermissionName
            },
            beforeSend: function () {
                $select.prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                } else {
                    alert('Lỗi: ' + (response.message || 'Không thể cập nhật vai trò.'));
                }
            },
            error: function (xhr) {
                alert('Lỗi máy chủ khi cập nhật vai trò. ' + (xhr.responseJSON?.message || ''));
            },
            complete: function () {
                $select.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.remove-member-btn', function () {
        const memberId = $(this).closest('tr').data('member-id');
        const $tableRow = $(this).closest('tr');

        if (!confirm('Bạn có chắc muốn xóa thành viên này khỏi bảng?')) return;

        const removeMemberUrl = buildUrl(window.routeUrls.boardsMembersRemove, {
            boardIdPlaceholder: boardId,
            memberIdPlaceholder: memberId
        });

        $.ajax({
            url: removeMemberUrl,
            method: 'DELETE',
            data: {
                _token: window.csrfToken,
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $tableRow.fadeOut(300, function () {
                        $(this).remove();
                    });
                } else {
                    alert('Lỗi: ' + (response.message || 'Không thể xóa thành viên.'));
                }
            },
            error: function (xhr) {
                alert('Lỗi máy chủ khi xóa thành viên. ' + (xhr.responseJSON?.message || ''));
            }
        });
    });
});
