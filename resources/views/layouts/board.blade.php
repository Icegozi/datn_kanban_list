<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kanban App')</title>

    <!-- Fonts and Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/column.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jquery/jquery-ui.css') }}">

</head>

<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed" style="height: 100%;">
    <div class="wrapper d-flex flex-column min-vh-100">

        {{-- Topbar --}}
        @include('components.topbar')

        <div class="d-flex flex-grow-1">
            {{-- Content --}}
            <div class="content-wrapper flex-grow-1 p-3"
                style="overflow-x: auto; overflow-y: hidden; white-space: nowrap">
                <div class="cute-border w-100 h-100">
                    @yield('content')
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('components.footer')

    </div>

    {{-- Scripts --}}
    {{-- Scripts --}}
    <script src="{{ asset('plugins/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery/jquery-ui.min.js') }}"></script> {{-- Make sure jQuery UI is loaded BEFORE
    column.js --}}
    <script src="{{ asset('assets/js/adminlte.js') }}"></script>
    <script>
        // Add CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.routeUrls = {
            // Column URLs (add these)
            columnsStoreBase: @json(route('columns.store', ['board' => ':boardIdPlaceholder'])),
            columnsUpdateBase: @json(route('columns.update', ['board' => ':boardIdPlaceholder', 'column' => ':columnIdPlaceholder'])),
            columnsDestroyBase: @json(route('columns.destroy', ['board' => ':boardIdPlaceholder', 'column' => ':columnIdPlaceholder'])),
            columnsReorderBase: @json(route('columns.reorder', ['board' => ':boardIdPlaceholder'])),

            // --- Task URLs ---
            tasksStoreBase: @json(route('tasks.store', ['column' => ':columnIdPlaceholder'])),
            tasksShowBase: @json(route('tasks.show', ['task' => ':taskIdPlaceholder'])),
            tasksUpdateBase: @json(route('tasks.update', ['task' => ':taskIdPlaceholder'])),
            tasksDestroyBase: @json(route('tasks.destroy', ['task' => ':taskIdPlaceholder'])),
            tasksUpdatePosition: @json(route('tasks.updatePosition')),
            tasksShowPageBase: @json(route('tasks.showDetailsPage', ['task' => ':taskIdPlaceholder'])),

            // Comment
            commentsStoreBase: @json(route('comments.store', ['task' => ':taskIdPlaceholder'])),
            commentsDeleteBase: "{{ url('/tasks/:taskIdPlaceholder/comments/:commentIdPlaceholder') }}",

            // --- Attachment Routes ---
            attachmentsIndexBase: @json(route('attachments.index', ['task' => ':taskIdPlaceholder'])),
            attachmentsStoreBase: @json(route('attachments.store', ['task' => ':taskIdPlaceholder'])),
            attachmentsDestroyBase: @json(route('attachments.destroy', ['attachment' => ':attachmentIdPlaceholder'])),

             // --- Checklist Routes ---
            checklistsIndexBase: @json(route('checklists.index', ['task' => ':taskIdPlaceholder'])),
            checklistsStoreBase: @json(route('checklists.store', ['task' => ':taskIdPlaceholder'])),
            checklistsUpdateBase: @json(route('checklists.update', ['checklist' => ':checklistIdPlaceholder'])),
            checklistsDestroyBase: @json(route('checklists.destroy', ['checklist' => ':checklistIdPlaceholder'])),
            checklistsReorderBase: @json(route('checklists.reorder', ['task' => ':taskIdPlaceholder'])),
        };
        // Pass the current board ID to JS if we are on a board page
        // Trong resources/views/layouts/board.blade.php
        window.currentBoardId = @json(isset($board) ? $board->id : null);
    </script>
    <script src="{{ asset('assets/js/column.js') }}"></script>
    <script src="{{ asset('assets/js/task.js') }}"></script>
    <script src="{{ asset('plugins/jquery/dayjs.min.js')}}"></script>
    <script src="{{ asset('assets/js/comment.js')}}"></script>
    <script src="{{ asset('assets/js/due_date.js')}}"></script>
    <script src="{{ asset('assets/js/attackment.js')}}"></script>
    <script src="{{ asset('assets/js/checklist.js') }}"></script>
    {{-- Include modal HTML --}}
    @include('user.partials.task_detail_modal') 
   

    
</body>

</html>
