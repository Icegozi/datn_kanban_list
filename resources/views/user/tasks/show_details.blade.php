@extends('layouts.board') 

@section('title', e($task->title) . ' - Chi tiết công việc')

@push('styles')
<style>
    .task-detail-page .left-section .card-body,
    .task-detail-page .right-section .card-body {
        max-height: calc(100vh - 250px); /* Điều chỉnh nếu cần */
        overflow-y: auto;
    }
    .description-box-display {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 10px;
        border-radius: .25rem;
        min-height: 100px;
        cursor: pointer;
        white-space: pre-wrap; /* Giữ các xuống dòng */
    }
    .description-box-display:hover {
        background-color: #e9ecef;
    }
    .assignees-list img {
        margin-right: -12px; /* Chồng lên nhau một chút */
        border: 2px solid white;
        transition: transform 0.2s ease-in-out;
    }
    .assignees-list img:hover {
        transform: scale(1.2);
        z-index: 1;
    }
    .assignees-list .avatar-more {
        margin-left: 0px; /* Điều chỉnh nếu cần */
        z-index: 0;
    }
    .btn-action-group .btn {
        margin-bottom: 5px;
        display: flex;
        align-items: center;
    }
    .btn-action-group .btn i {
        margin-right: 8px;
        width: 16px; /* Căn chỉnh icon */
        text-align: center;
    }
    .activity-log-item {
        display: flex;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    .activity-log-item .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin-right: 10px;
    }
    .activity-log-item .activity-content strong {
        color: #007bff;
    }
    .activity-log-item .activity-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .comments-section .comment-item {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    .comments-section .comment-item:last-child {
        border-bottom: none;
    }
    #taskTitleInput { font-size: 1.75rem; font-weight: 500; border:none; box-shadow: none; padding-left:0; }
    #taskTitleInput:focus { border:1px solid #ced4da; box-shadow: 0 0 0 .2rem rgba(0,123,255,.25); padding-left: .75rem; }

</style>
@endpush

@section('page_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                {{-- Cho phép sửa tiêu đề task --}}
                <input type="text" id="taskTitleInput" class="form-control form-control-lg" value="{{ e($task->title) }}" data-original-title="{{ e($task->title) }}">
            </div>
            <div class="col-sm-4">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('boards.show', $task->column->board_id) }}">Bảng: {{ e($task->column->board->name) }}</a></li>
                    <li class="breadcrumb-item active">Chi tiết</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="task-detail-page">
    <div class="row">
        {{-- Phần Nội dung chính (Trái) --}}
        <div class="col-lg-8 left-section">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <p class="text-muted mb-2">
                        Trong danh sách: <strong class="text-primary">{{ e($task->column->name) }}</strong>
                    </p>

                    @if($task->assignees && $task->assignees->count() > 0)
                    <div class="mb-3">
                        <h6 class="text-secondary font-weight-bold">NGƯỜI THAM GIA</h6>
                        <div class="task-assignees assignees-list d-flex align-items-center">
                            @foreach($task->assignees->take(7) as $assignee)
                                <img src="https://i.pravatar.cc/40?u={{ urlencode($assignee->email) }}"
                                     class="rounded-circle"
                                     width="35" height="35"
                                     title="{{ e($assignee->name) }}"
                                     alt="{{ e($assignee->name) }}">
                            @endforeach
                            @if($task->assignees->count() > 7)
                                <span class="avatar-more border border-light rounded-circle bg-light text-muted small d-inline-flex align-items-center justify-content-center"
                                       style="width: 35px; height: 35px; font-size: 0.8rem;"
                                       title="{{ $task->assignees->count() - 7 }} người nữa">
                                       +{{ $task->assignees->count() - 7 }}
                                </span>
                            @endif
                            {{-- Nút thêm người tham gia --}}
                            <button class="btn btn-sm btn-outline-secondary rounded-circle ml-2" title="Thêm người tham gia" style="width: 35px; height: 35px;"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    @endif

                    <div class="form-group mb-4">
                        <h6 class="text-secondary font-weight-bold">MÔ TẢ</h6>
                        <div id="taskDescriptionContainer">
                            <div class="description-box-display" title="Nhấn để sửa mô tả">
                                {!! $task->description ? nl2br(e($task->description)) : '<em class="text-muted">Thêm mô tả chi tiết hơn...</em>' !!}
                            </div>
                            <div class="description-box-edit" style="display: none;">
                                <textarea id="taskDescriptionInput" class="form-control" rows="6" placeholder="Nhập mô tả cho công việc...">{{ e($task->description) }}</textarea>
                                <div class="mt-2">
                                    <button class="btn btn-success btn-sm" id="saveDescriptionBtn"><i class="fas fa-save mr-1"></i> Lưu</button>
                                    <button class="btn btn-secondary btn-sm" id="cancelDescriptionBtn">Hủy</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 class="text-secondary font-weight-bold">HOẠT ĐỘNG</h6>
                        {{-- Form thêm comment/activity --}}
                        <div class="d-flex align-items-start mb-3">
                             <img src="https://i.pravatar.cc/40?u={{ urlencode(Auth::user()->email) }}" alt="{{ Auth::user()->name }}" class="rounded-circle mr-2" width="32" height="32">
                             <textarea id="newActivityInput" class="form-control" rows="2" placeholder="Viết một bình luận..."></textarea>
                        </div>
                        <button id="addActivityBtn" class="btn btn-primary btn-sm mb-3">Gửi</button>

                        <div id="taskActivityLog" class="activity-log">
                            <div class="activity-log-item">
                                <img src="https://i.pravatar.cc/40?u=system" alt="System" class="avatar">
                                <div class="activity-content">
                                    <p class="mb-0"><strong>{{ e($task->creator->name ?? 'Ai đó') }}</strong> đã tạo công việc này.</p>
                                    <p class="activity-meta">{{ $task->formatted_created_at ?? $task->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            {{-- Load các hoạt động khác ở đây --}}
                            <p class="text-muted small">Nhật ký hoạt động và bình luận sẽ được hiển thị ở đây.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Phần Actions (Phải) --}}
        <div class="col-lg-4 right-section">
            <div class="card card-secondary card-outline">
                <div class="card-header"><h3 class="card-title">THÔNG TIN BỔ SUNG</h3></div>
                <div class="card-body btn-action-group">
                    <button class="btn btn-light btn-block text-left"><i class="far fa-user"></i> Người tham gia</button>
                    <button class="btn btn-light btn-block text-left"><i class="fas fa-tag"></i> Nhãn</button>
                    <button class="btn btn-light btn-block text-left"><i class="far fa-check-square"></i> Checklist</button>
                    <button class="btn btn-light btn-block text-left"><i class="far fa-calendar-alt"></i> Ngày hết hạn
                        @if($task->due_date)
                            <span class="badge badge-info float-right mt-1">{{ $task->formatted_due_date }}</span>
                        @else
                            <span class="badge badge-secondary float-right mt-1">Chưa đặt</span>
                        @endif
                    </button>
                    <button class="btn btn-light btn-block text-left"><i class="fas fa-paperclip"></i> Đính kèm</button>
                </div>
            </div>

            <div class="card card-secondary card-outline mt-3">
                 <div class="card-header"><h3 class="card-title">HÀNH ĐỘNG</h3></div>
                <div class="card-body btn-action-group">
                    <button class="btn btn-light btn-block text-left" id="moveTaskBtn"><i class="fas fa-arrow-right"></i> Di chuyển</button>
                    <button class="btn btn-light btn-block text-left" id="archiveTaskBtn"><i class="fas fa-archive"></i> Lưu trữ</button>
                    <button type="button" class="btn btn-danger btn-block text-left" id="deleteTaskBtn" data-task-id="{{ $task->id }}"><i class="fas fa-trash-alt"></i> Xóa công việc</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const taskId = "{{ $task->id }}";
    const originalDescription = {!! json_encode($task->description) !!}; // Dùng cho nút Hủy của Mô tả
    const originalTitle = {!! json_encode($task->title) !!};

    // Helper function cho escape HTML (nếu chưa có global)
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return unsafe
             .replace(/&/g, "&")
             .replace(/</g, "<")
             .replace(/>/g, ">")
             .replace(/"/g, """)
             .replace(/'/g, "'");
    }
    function nl2br(str) {
        if (typeof str === 'undefined' || str === null) return '';
        return str.replace(/(\r\n|\n\r|\r|\n)/g, '<br>$1');
    }

    // Sửa tiêu đề Task
    $('#taskTitleInput').on('blur', function() {
        const newTitle = $(this).val().trim();
        if (newTitle === originalTitle || newTitle === '') {
            $(this).val(originalTitle); // Reset nếu không thay đổi hoặc rỗng
            return;
        }
        // AJAX call để cập nhật tiêu đề
        $.ajax({
            url: window.routeUrls.tasksUpdateBase.replace(':taskIdPlaceholder', taskId),
            method: 'PUT', // Hoặc PATCH
            data: { title: newTitle, _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success && response.task) {
                    $('#taskTitleInput').val(response.task.title);
                    // originalTitle = response.task.title; // Cập nhật lại original title
                    document.title = response.task.title + ' - Chi tiết công việc'; // Cập nhật title của trang
                    showGlobalNotification('Tiêu đề công việc đã được cập nhật.', 'success');
                } else {
                    $('#taskTitleInput').val(originalTitle); // Hoàn tác nếu lỗi
                    showGlobalNotification(response.message || 'Không thể cập nhật tiêu đề.', 'error');
                }
            },
            error: function(jqXHR) {
                $('#taskTitleInput').val(originalTitle); // Hoàn tác nếu lỗi
                showGlobalNotification('Lỗi: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            }
        });
    }).on('keypress', function(e) {
        if (e.which == 13) { // Enter
            $(this).blur();
        }
    });


    // Sửa Mô tả
    $('#taskDescriptionContainer').on('click', '.description-box-display', function() {
        $(this).hide();
        $('.description-box-edit').show().find('textarea').focus();
    });

    $('#cancelDescriptionBtn').on('click', function() {
        $('.description-box-edit').hide();
        $('#taskDescriptionInput').val(originalDescription);
        $('.description-box-display').html(originalDescription ? nl2br(escapeHtml(originalDescription)) : '<em class="text-muted">Thêm mô tả chi tiết hơn...</em>').show();
    });

    $('#saveDescriptionBtn').on('click', function() {
        const newDescription = $('#taskDescriptionInput').val();
        const $button = $(this);
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Đang lưu...');

        $.ajax({
            url: window.routeUrls.tasksUpdateBase.replace(':taskIdPlaceholder', taskId),
            method: 'PUT', // Hoặc PATCH
            data: {
                description: newDescription,
                 _token: $('meta[name="csrf-token"]').attr('content') // Đảm bảo token được gửi
            },
            success: function(response) {
                if (response.success && response.task) {
                    $('.description-box-display').html(response.task.description ? nl2br(escapeHtml(response.task.description)) : '<em class="text-muted">Thêm mô tả chi tiết hơn...</em>');
                    // originalDescription = response.task.description; // Cần cập nhật nếu muốn "Hủy" hoạt động đúng sau nhiều lần sửa
                    $('#taskDescriptionInput').val(response.task.description); // Cập nhật textarea
                    $('.description-box-edit').hide();
                    $('.description-box-display').show();
                    showGlobalNotification('Mô tả đã được cập nhật.', 'success');
                } else {
                    showGlobalNotification(response.message || 'Không thể cập nhật mô tả.', 'error');
                }
            },
            error: function(jqXHR) {
                showGlobalNotification('Lỗi: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            },
            complete: function() {
                $button.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Lưu');
            }
        });
    });

    // Xóa Task
    $('#deleteTaskBtn').on('click', function() {
        if (!confirm('Bạn có chắc chắn muốn xóa công việc này không? Hành động này không thể hoàn tác.')) {
            return;
        }
        const boardIdForRedirect = "{{ $task->column->board_id }}";
        const $button = $(this);
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Đang xóa...');

        $.ajax({
            url: window.routeUrls.tasksDestroyBase.replace(':taskIdPlaceholder', taskId),
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    showGlobalNotification(response.message || 'Công việc đã được xóa.', 'success');
                    // Chuyển hướng về trang board
                    let boardShowUrl = "{{ route('boards.show', ['board' => ':boardIdPlaceholder']) }}";
                    window.location.href = boardShowUrl.replace(':boardIdPlaceholder', boardIdForRedirect);
                } else {
                    showGlobalNotification(response.message || 'Không thể xóa công việc.', 'error');
                    $button.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Xóa công việc');
                }
            },
            error: function(jqXHR) {
                showGlobalNotification('Lỗi: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                $button.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Xóa công việc');
            }
        });
    });

    // TODO: Thêm logic cho việc thêm Activity/Comment, Move Task, Archive Task, Members, Labels, etc.
    // Ví dụ cho Add Activity/Comment (cần backend tương ứng)
    $('#addActivityBtn').on('click', function() {
        const commentBody = $('#newActivityInput').val().trim();
        if (!commentBody) {
            showGlobalNotification('Vui lòng nhập nội dung bình luận.', 'warning');
            return;
        }
        // AJAX call to store comment
        // $.ajax({ ... success: function() { $('#newActivityInput').val(''); loadActivities(); } ... });
        showGlobalNotification('Chức năng bình luận đang được phát triển!', 'info');
        $('#newActivityInput').val(''); // Xóa input sau khi gửi (giả)
    });

});
</script>
@endpush