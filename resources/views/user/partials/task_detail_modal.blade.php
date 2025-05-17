<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-labelledby="taskDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl  modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-light">
                <h5 class="modal-title" id="modalTaskTitleHeader">Chi tiết công việc</h5>
                <input type="hidden" id="modalTaskId">
                <button type="button" class="close" style="color: #FF0000;" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-8">
                            <p class="text-muted mb-1">
                                Trong danh sách: <strong id="modalTaskColumnName">Tên Cột</strong>
                            </p>
                            <div class="form-group mb-3">
                                <input type="text"
                                    class="form-control form-control-lg font-weight-bold border-0 pl-0 shadow-none"
                                    id="modalTaskTitleInput" placeholder="Nhập tiêu đề công việc...">
                            </div>

                            <div class="mb-3">
                                <h6 class="font-weight-bold text-dark"><i
                                        class="fas fa-user-friends mr-2"></i>NGƯỜI PHỤ TRÁCH</h6>
                                <div id="modalTaskAssignees" class="d-flex align-items-center flex-wrap">
                                    {{-- Ảnh avatar người tham gia sẽ được thêm vào đây bằng JS --}}
                                    <span class="text-muted small">Chưa có ai tham gia.</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="font-weight-bold text-dark"><i class="fas fa-align-left mr-2"></i>MÔ TẢ
                                </h6>
                                <div id="modalTaskDescriptionContainer">
                                    <div class="description-box-display p-2 border rounded bg-light"
                                        style="min-height: 80px; cursor: pointer; white-space: pre-wrap;">
                                        <em class="text-muted">Thêm mô tả chi tiết hơn...</em>
                                    </div>
                                    <div class="description-box-edit" style="display: none;">
                                        <textarea id="modalTaskDescriptionTextarea" class="form-control" rows="5"
                                            placeholder="Nhập mô tả..."></textarea>
                                        <div class="mt-2">
                                            <button class="btn btn-secondary btn-sm cancel-description-btn">Hủy</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="font-weight-bold text-dark"><i class="fas fa-paperclip mr-2"></i>ĐÍNH
                                    KÈM</h6>
                                <div id="modalTaskAttachments">
                                    <p class="text-muted small">Chưa có đính kèm.</p>
                                </div>
                                {{-- <button class="btn btn-sm btn-outline-secondary mt-1 add-attachment-btn"><i
                                        class="fas fa-plus"></i> Thêm đính kèm</button> --}}
                            </div>

                            <div class="mb-3">
                                <h6 class="font-weight-bold text-dark">
                                    <i class="fas fa-tasks mr-2"></i>CHECKLIST
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-1 ml-2"
                                        id="toggleChecklistVisibilityBtn" title="Hiện/Ẩn Checklist">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <span id="checklistProgress" class="ml-2 small text-muted"></span>
                                </h6>
                                <div id="modalTaskChecklistSection" style="display: none;">
                                    <div id="modalTaskChecklistsContainer">
                                        {{-- Content will be loaded by JS --}}
                                        <p class="text-muted small">Bấm vào nút "Checklist" ở cột phải để quản lý.</p>
                                    </div>
                                    <div class="add-checklist-item-form mt-4" style="display: none;"> {{-- Form also
                                        hidden initially --}}
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="newChecklistItemTitle"
                                                placeholder="Thêm mục mới...">
                                            <div class="input-group-append">
                                                <button class="btn btn-success" id="saveNewChecklistItemBtn"
                                                    type="button">Thêm</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-dark"><i class="fas fa-comments mr-2"></i>BÌNH
                                    LUẬN</h6>
                                <div class="add-comment-section mb-3">
                                    <div class="input-group">
                                        <textarea id="modalNewCommentTextarea"
                                            class="form-control border-right-0 rounded-left" rows="1"
                                            placeholder="Viết bình luận..."></textarea>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary rounded-right"
                                                id="modalSaveCommentBtn" title="Gửi">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>


                                <div id="modalDisplayComment" style="min-height: 300px; overflow-y: auto; height:100%">

                                </div>
                            </div>
                        </div>

                        {{-- Cột Phải: Actions --}}
                        <div class="col-lg-4">
                            <div class="sticky-top" style="top: 15px;"> {{-- Cho actions cố định khi cuộn --}}
                                <h6 class="text-muted small font-weight-bold">THÔNG TIN BỔ SUNG</h6>
                                <div class="list-group list-group-flush mb-3">
                                    <a href="#" class="list-group-item list-group-item-action modal-action-btn"
                                        id="modalAssignMembersTrigger"><i
                                            class="far fa-user fa-fw mr-2 text-primary"></i>Người phụ trách</a>
                                    <a href="#" class="list-group-item list-group-item-action modal-action-btn"
                                        id="modalManageChecklistTrigger"> {{-- This is the trigger button --}}
                                        <i class="far fa-check-square fa-fw mr-2 text-info"></i>Checklist
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action modal-action-btn"
                                        id="modalSetDueDateTrigger">
                                        <i class="far fa-calendar-alt fa-fw mr-2 text-warning"></i>Ngày hết hạn
                                        <span class="badge badge-dark float-right mt-1" id="modalDueDateBadge">Chưa
                                            đặt</span>
                                    </a>
                                    <!-- Chèn input datepicker ẩn -->
                                    <div id="dueDatePickerContainer" style="display: none; margin: 0.5rem;">
                                        <input type="text" id="modalDueDateInput" class="form-control form-control-sm"
                                            placeholder="Chọn ngày..." readonly />
                                        <div class="mt-2 text-right">
                                        </div>
                                    </div>
                                    <a href="#" class="list-group-item list-group-item-action modal-action-btn"
                                        id="modalAddAttachmentTrigger"><i
                                            class="fas fa-paperclip fa-fw mr-2 text-secondary"></i>Đính kèm</a>
                                </div>

                                <h6 class="text-muted small font-weight-bold">HÀNH ĐỘNG</h6>
                                <div class="list-group list-group-flush">
                                    {{-- <a href="#" class="list-group-item list-group-item-action modal-action-btn"
                                        id="modalMoveTaskTrigger"><i class="fas fa-arrow-right fa-fw mr-2"></i>Di
                                        chuyển</a> --}}
                                    <a href="#" class="list-group-item list-group-item-action modal-action-btn"
                                        id="modalArchiveTaskTrigger"><i class="fas fa-archive fa-fw mr-2"></i>Lưu thay
                                        đổi</a>
                                    <a href="#"
                                        class="list-group-item list-group-item-action list-group-item-danger modal-action-btn"
                                        id="modalDeleteTaskTrigger"><i class="fas fa-trash-alt fa-fw mr-2"></i>Xóa
                                        công việc</a>
                                </div>

                                <div id="modalTaskActivityLog"
                                    style="min-height: 300px; overflow-y: auto; margin-top:20px; height:100%">
                                    <p class="text-muted small">Lịch sử hoạt động sẽ hiển thị ở đây.</p>
                                </div>
                                <button id="loadMoreActivity" class="btn btn-link">
                                    <i class="fas fa-angle-down mr-1"></i> Xem thêm
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Đóng</button>
            </div> --}}
        </div>
    </div>
</div>