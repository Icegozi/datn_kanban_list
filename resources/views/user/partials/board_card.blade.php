<div class="col-md-4 col-lg-3 mb-4 card-drop-target board-card" id="board-card-{{ $board->id }}">
    <div class="card shadow-sm h-100 card-hover">
        <div class="card-body p-3 d-flex flex-column">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h6 class="mb-0 text-truncate font-weight-bold board-name">{{ $board->name }}</h6>
                {{-- Dropdown --}}
                <div class="dropdown">
                    <a href="#" class="text-muted dropdown-toggle-no-caret"
                        id="itemMenu{{ $board->id }}" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false" aria-label="Tùy chọn bảng">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="itemMenu{{ $board->id }}">
                        {{-- Open Link --}}
                        <a class="dropdown-item open-board-link" href="{{ route('boards.show', $board->id) }}">
                            <i class="fas fa-folder-open fa-fw mr-2 text-muted"></i>Mở
                        </a>
                        {{-- Rename Link - Add data attributes for JS --}}
                        <a class="dropdown-item rename-board-link" href="#"
                           data-id="{{ $board->id }}"
                           data-name="{{ $board->name }}"
                           data-update-url="{{ route('boards.update', $board->id) }}">
                           {{-- data-toggle="modal" data-target="#renameBoardModal"> --}} {{-- Add this if using modal trigger directly --}}
                            <i class="fas fa-pencil-alt fa-fw mr-2 text-muted"></i>Sửa tên
                        </a>
                        <div class="dropdown-divider"></div>
                        {{-- Delete Link - Add data attributes for JS --}}
                        <a class="dropdown-item delete-board-link text-danger" href="#"
                           data-id="{{ $board->id }}"
                           data-name="{{ $board->name }}"
                           data-destroy-url="{{ route('boards.destroy', $board->id) }}">
                            <i class="fas fa-trash-alt fa-fw mr-2"></i> Xoá
                        </a>
                    </div>
                </div>
            </div>

            {{-- Timestamp (use updated_at for more relevance after edits) --}}
            <div class="d-flex align-items-center text-muted small mt-auto board-timestamp">
                <i class="far fa-clock fa-fw mr-2"></i>
                <span>{{ $board->updated_at->format('d/m/Y H:i') }}</span>
                 {{-- Or use created_at: {{ $board->created_at->format('d/m/Y H:i') }} --}}
            </div>
        </div>
    </div>
</div>