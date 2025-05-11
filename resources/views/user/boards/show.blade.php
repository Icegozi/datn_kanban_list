@extends('layouts.board')

@section('title', $board->name . ' - Kanban Board') {{-- Dynamic Title --}}

@section('content')
{{-- Board Header (Optional: Add board rename/delete controls here later) --}}
<div class="board-header p-3 mb-3 border-bottom">
    <h1>{{ $board->name }}</h1>
    {{-- Add board description if available: <p class="text-muted">{{ $board->description }}</p> --}}
</div>

{{-- Kanban Board Container --}}
{{-- Add data-board-id for easier JS targeting --}}
<div class="kanban-board" id="kanbanBoard" data-board-id="{{ $board->id }}">

    {{-- Loop through columns from the database --}}
    @foreach($board->columns as $column)
        <div class="kanban-column" data-column-id="{{ $column->id }}">
            <div class="column-header d-flex justify-content-between align-items-center mb-3">
                <h5 class="column-title flex-grow-1 mr-2" data-column-id="{{ $column->id }}">{{ $column->name }}</h5>
                {{-- Add dropdown/icons for column actions --}}
                <div class="column-actions">
                    <button class="btn btn-sm btn-light edit-column-btn" title="Đổi tên cột"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-light delete-column-btn" title="Xoá cột"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

             {{-- Column Content (Tasks) - Make this sortable area --}}
             <div class="column-content flex-grow-1" data-column-id="{{ $column->id }}">
                {{-- Loop through tasks for this column (Load tasks here or via AJAX) --}}
                @foreach($column->tasks as $task)
                    <div class="kanban-card" data-task-id="{{ $task->id }}">
                        <h5>{{ $task->title }}</h5>
                         @if($task->due_date)
                            <small class="task-due-date text-warning d-block mt-1">
                                <i class="far fa-clock"></i>
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                            </small>
                        @endif
                         {{-- Add tags, avatars, due dates etc. based on your task model --}}
                         {{-- Example: --}}
                         {{-- <div><span class="tag">{{ $task->priority }}</span></div> --}}
                         {{-- <small class="text-muted">Due: {{ $task->due_date ? $task->due_date->format('M d') : 'N/A' }}</small> --}}
                    </div>
                @endforeach
                 {{-- Placeholder or Input for Adding New Card --}}
                 {{-- We'll add the 'Add Card' JS logic later --}}
                 <div class="add-card-placeholder mt-auto">
                     <i class="fas fa-plus"></i>
                     <div>Thêm công việc</div>
                 </div>
             </div>


        </div> {{-- End .kanban-column --}}
    @endforeach

    {{-- Placeholder/Button to Add New Column --}}
    <div class="kanban-column add-column-trigger" style="flex: 0 0 300px; background: transparent; box-shadow: none; padding: 0;">
         <div class="add-column-placeholder h-10" id="addColumnBtn">
            <i class="fas fa-plus"></i>

        </div>
         {{-- Hidden Input Form for New Column --}}
         <div class="add-column-form p-2 bg-white rounded" style="display: none;">
             <input type="text" class="form-control form-control-sm mb-2" id="newColumnName" placeholder="Nhập tên cột...">
             <button class="btn btn-success btn-sm mr-1" id="saveNewColumnBtn">Lưu</button>
             <button class="btn btn-secondary btn-sm" id="cancelNewColumnBtn">Huỷ</button>
         </div>
    </div>

</div> {{-- End .kanban-board --}}
@endsection
