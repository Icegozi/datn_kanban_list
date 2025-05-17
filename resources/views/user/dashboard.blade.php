@extends('layouts.user')

@section('content')
    <div class="container">

        <div class="row mb-3 align-items-center">
            <div class="col-md-12 text-md-left">
                <button class="btn btn-outline-success text-white" id="add-board-btn">
                    <i class="fas fa-plus mr-1"></i> Tạo bảng mới
                </button>
            </div>
        </div>

        <div class="row" id="board-list-container">

            @forelse ($boards as $board)
                @include('user.partials.board_card', ['board' => $board])
            @empty
                <div class="col-12" id="no-boards-message">
                    <p class="text-muted text-center mt-5">Bạn chưa có bảng làm việc nào. Hãy tạo một bảng mới!</p>
                </div>
            @endforelse

        </div>

    </div>
    @include('user.partials.rename_board_modal')
@endsection

@push('scripts')
    <script></script>
@endpush
