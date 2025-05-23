$(document).ready(function () {
    $('#modalSaveCommentBtn').on('click', function () {
        const taskId = $('#modalTaskId').val();
        const content = $('#modalNewCommentTextarea').val().trim();

        if (!content) {
            alert('Vui lòng nhập nội dung bình luận.');
            return;
        }

        const url = window.routeUrls.commentsStoreBase.replace(':taskIdPlaceholder', taskId);

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                content: content,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    const comment = response.comment;

                    const createdAt = new Date(comment.created_at).toLocaleString();

                    const commentHtml = `
                         <div class="media mb-3" id="comment-${comment.id}">
                        <img src="${comment.user_avatar}" class="rounded-circle mr-2" width="32" height="32" alt="${comment.user_name}">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">${comment.user.name} <small class="text-muted">(${createdAt})</small></h6>
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
                    `;

                    $('#modalDisplayComment').prepend(commentHtml);
                    $('#modalNewCommentTextarea').val('');
                } else {
                    alert(response.message || 'Đã xảy ra lỗi khi thêm bình luận.');
                }
            },
            error: function (xhr) {
                console.error(xhr);
                alert('Không thể gửi bình luận. Vui lòng thử lại.');
            }
        });
    });


    // Xử lý sự kiện "Xóa"
    $(document).on('click', '.delete-comment-btn', function () {
        const commentId = $(this).data('comment-id');

        if (confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
            const taskId = $('#modalTaskId').val();
            const url = window.routeUrls.commentsDeleteBase
                .replace(':taskIdPlaceholder', taskId)
                .replace(':commentIdPlaceholder', commentId);

            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        $(`#comment-${commentId}`).remove();
                        alert('Bình luận đã được xóa.');
                    } else {
                        alert(response.message || 'Đã xảy ra lỗi khi xóa bình luận.');
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                    alert('Không thể xóa bình luận. Vui lòng thử lại.');
                }
            });
        }
    });

});
