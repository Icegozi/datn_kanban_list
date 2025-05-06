{{-- File: resources/views/partials/task_card.blade.php --}}
{{-- Nhận biến $task từ @include --}}
{{-- Giả định $task->assignees đã được eager load từ Controller để tối ưu --}}

<div class="kanban-card" data-task-id="{{ $task->id }}">
    {{-- Tiêu đề Task --}}
    <h5 class="task-title mb-1">{{ $task->title }}</h5>

    {{-- Ngày hết hạn (Nếu có) --}}
    @if($task->due_date)
        <small class="text-muted d-block mb-1">
           <i class="far fa-calendar-alt mr-1"></i> Due: {{ $task->due_date->format('M d') }} {{-- Sửa lại định dạng nếu muốn --}}
         </small>
    @endif

    {{-- Người thực hiện (Nếu có và đã load) --}}
    {{-- Kiểm tra relationLoaded để tránh lỗi nếu chưa eager load --}}
    @if($task->relationLoaded('assignees') && $task->assignees->isNotEmpty())
       <div class="task-assignees mt-2">
         {{-- Giới hạn số lượng avatar hiển thị --}}
         @foreach($task->assignees->take(4) as $assignee)
            {{-- Bạn có thể thay pravatar bằng nguồn ảnh avatar thực tế nếu có --}}
            <img src="https://i.pravatar.cc/40?u={{ $assignee->email }}" {{-- Tăng kích thước lên chút --}}
                 class="rounded-circle border border-white" {{-- Thêm border nhỏ --}}
                 width="25" height="25" {{-- Kích thước avatar --}}
                 title="{{ $assignee->name }}" {{-- Hiển thị tên khi hover --}}
                 alt="{{ $assignee->name }}">
        @endforeach
        {{-- Hiển thị số lượng còn lại nếu nhiều hơn 4 --}}
        @if($task->assignees->count() > 4)
             <span class="avatar-more ml-n2 border border-light rounded-circle bg-light text-muted small d-inline-flex align-items-center justify-content-center"
                   style="width: 25px; height: 25px; font-size: 0.7rem;"
                   title="{{ $task->assignees->count() - 4 }} more assignees">
                   +{{ $task->assignees->count() - 4 }}
            </span>
        @endif
       </div>
    @endif

    {{-- Thêm các thông tin khác như Priority tags tại đây nếu muốn --}}
    {{-- Ví dụ: --}}
    {{-- <div class="mt-1">
             <span class="tag priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
         </div> --}}

</div>