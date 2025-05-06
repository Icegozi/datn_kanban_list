<div class="modal fade" id="renameBoardModal" tabindex="-1" role="dialog" aria-labelledby="renameBoardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document"> {{-- modal-sm cho nhỏ --}}
        <div class="modal-content border-0 shadow rounded">
            <form id="rename-board-form" method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-0 pb-2">
                    <h6 class="modal-title font-weight-bold" id="renameBoardModalLabel">Nhập tên bảng mới</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body pt-1">
                    <input type="hidden" id="rename-board-id" name="board_id">
                    <input type="hidden" id="rename-board-current-name">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="rename-board-new-name" name="name" required maxlength="255" placeholder="Nhập tên...">
                    </div>
                    <div id="rename-error-message" class="text-danger small mt-2"></div>
                </div>
                <div class="modal-footer justify-content-end border-0 pt-1">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">OK</button>
                </div>
            </form>
        </div>
    </div>
</div>
