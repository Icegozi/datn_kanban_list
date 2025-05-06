@extends('layouts.user')

@section('content')
    <div class="container">

        {{-- Overlay (giữ nguyên nếu dùng cho cừu) --}}
        {{-- <div id="cardOverlay"></div> --}}

        <div class="row mb-3 align-items-center">
            {{-- 🔍 Thanh tìm kiếm --}}
            <div class="col-md-6"> {{-- Adjust column width --}}
                <form method="GET" action="#"> {{-- Add action="{{ route('user.dashboard') }}" later if implementing search --}}
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm bảng làm việc" value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                        </div>
                    </div>
                </form>
            </div>
             {{-- ✨ Nút tạo bảng mới --}}
            <div class="col-md-6 text-md-right"> {{-- Adjust column width and alignment --}}
                 <button class="btn btn-outline-success" id="add-board-btn">
                    <i class="fas fa-plus mr-1"></i> Tạo bảng mới
                 </button>
            </div>
        </div>

        {{-- Container cho danh sách các board --}}
        <div class="row" id="board-list-container">

            {{-- Giao diện các board --}}
            @forelse ($boards as $board)
                {{-- Include the board card partial --}}
                @include('user.partials.board_card', ['board' => $board])
            @empty
                <div class="col-12" id="no-boards-message">
                    <p class="text-muted text-center mt-5">Bạn chưa có bảng làm việc nào. Hãy tạo một bảng mới!</p>
                </div>
            @endforelse

        </div> {{-- End #board-list-container --}}

    </div> {{-- End .container --}}

    {{-- Include Modal for Renaming --}}
    @include('user.partials.rename_board_modal')

@endsection

@push('scripts')
<script>
    
</script>
@endpush

