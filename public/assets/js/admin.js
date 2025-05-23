$(document).ready(function () {
    $.ajax({
        url: window.routeUrls.userList,
        method: 'GET',
        success: function (response) {
            console.log(response);

            if (response.success && response.users.data.length > 0) {
                const users = response.users.data;
                const dropdownMenu = $('[aria-labelledby="userManagementDropdownSidebar"]');

                users.forEach(user => {
                    const userLink = window.routeUrls.userShowBase.replace(':userIdPlaceholder', user.id);
                    const linkElement = $('<a>', {
                        class: 'dropdown-item',
                        text: user.name,
                        href: userLink
                    });

                    dropdownMenu.append(linkElement);
                });
            }
        },
        error: function () {
            console.error('Lỗi khi lấy danh sách người dùng');
        }
    });

   // Xử lý sự kiện click vào nút xóa
    $(document).on('click', '.delete-user', function () {
        if (!confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
            return;
        }

        var userId = $(this).data('id');

        var deleteUrl = window.routeUrls.userDestroyBase.replace(':userIdPlaceholder', userId);

        $.ajax({
            url: deleteUrl,
            method: 'DELETE',
            data: {
                _token: window.csrfToken 
            },
            success: function (response) {
                if (response.success) {
                    $('a[data-id="' + userId + '"]').closest('tr').remove();
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('Không thể tự xóa tài khoản đang sử dụng!');
            }
        });
    });
});
