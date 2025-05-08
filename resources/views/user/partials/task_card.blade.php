{{-- resources/views/layouts/board.blade.php --}}
{{-- ... (phần đầu của file) ... --}}

    {{-- Scripts --}}
    {{-- ... (các script của bạn) ... --}}

    {{-- Task Detail Modal --}}
    <div class="modal fade" id="taskDetailModal" tabindex="-1" aria-labelledby="taskDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document"> {{-- Tăng kích thước modal thành modal-xl hoặc modal-lg --}}
          <div class="modal-content">
              {{-- Không cần modal-header, modal-body, modal-footer riêng nữa nếu bạn muốn dùng layout card-container --}}
              {{-- Tuy nhiên, để giữ nút close chuẩn của Bootstrap, chúng ta có thể giữ lại modal-header --}}
              <div class="modal-header">
                  <h5 class="modal-title" id="taskDetailModalLabel">
                      {{-- Tiêu đề task sẽ được cập nhật ở đây bởi JS --}}
                      Task Title
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> {{-- Nút close mới của Bootstrap 5 --}}
              </div>
              <div class="modal-body p-0"> {{-- Xóa padding của modal-body để card-container chiếm toàn bộ --}}
                  <div class="card-container-wrapper p-3"> {{-- Thêm một wrapper để có padding nếu cần --}}
                      {{-- Nội dung từ HTML bạn cung cấp sẽ được đặt vào đây,
                           nhưng các giá trị sẽ được điền bởi JavaScript --}}
                      <div class="d-flex justify-content-between">
                          <div class="left-section" style="width: 65%;">
                              {{-- Tiêu đề task đã có ở modal-header, nhưng có thể giữ lại nếu muốn thiết kế khác --}}
                              {{-- <h5 class="mb-3" id="modalTaskTitleDisplay">Task Title</h5> --}}
                              <p class="text-muted">in list <strong id="modalTaskColumnName">COLUMN_NAME</strong></p>

                              {{-- Nút Watching (Tùy chọn, có thể thêm sau) --}}
                              {{-- <div class="form-check mb-3">
                                  <input class="form-check-input" type="checkbox" id="modalTaskWatching">
                                  <label class="form-check-label" for="modalTaskWatching">Watching</label>
                              </div> --}}

                              <h6>Description</h6>
                              <div id="modalTaskDescriptionContainer">
                                  {{-- Form chỉnh sửa description sẽ được load ở đây --}}
                                  <div class="description-box-display p-2 border rounded" style="min-height: 80px; cursor: pointer;">
                                      <p id="modalTaskDescriptionText" class="mb-0">Click to add description...</p>
                                  </div>
                                  <div class="description-box-edit" style="display: none;">
                                      <textarea id="modalTaskDescriptionInput" class="form-control" rows="4" placeholder="😊 Say it with an emoji, just type ':'"></textarea>
                                      <button class="btn btn-primary btn-sm mt-2" id="saveDescriptionBtn">Save</button>
                                      <button class="btn btn-secondary btn-sm mt-2" id="cancelDescriptionBtn">Cancel</button>
                                  </div>
                              </div>
                              <hr>

                              <h6>Activity</h6>
                              {{-- Input comment (Tùy chọn, có thể thêm sau) --}}
                              {{-- <input type="text" class="form-control mb-2" placeholder="Write a comment..."> --}}
                              <div id="modalTaskActivityLog">
                                  {{-- Lịch sử hoạt động sẽ được load ở đây --}}
                                  <p class="text-muted">Activity log will appear here.</p>
                              </div>
                          </div>

                          <div class="right-section" style="width: 30%;">
                              <h6 class="text-muted small">ADD TO CARD</h6>
                              <button class="btn btn-light btn-action"><i class="far fa-user mr-1"></i> Members</button>
                              <button class="btn btn-light btn-action"><i class="fas fa-tag mr-1"></i> Labels</button>
                              <button class="btn btn-light btn-action"><i class="far fa-check-square mr-1"></i> Checklist</button>
                              <button class="btn btn-light btn-action"><i class="far fa-calendar-alt mr-1"></i> Dates</button>
                              <button class="btn btn-light btn-action"><i class="fas fa-paperclip mr-1"></i> Attachment</button>
                              {{-- <button class="btn btn-light btn-action">Location</button> --}}
                              {{-- <button class="btn btn-light btn-action">Cover</button> --}}
                              {{-- <button class="btn btn-light btn-action">Custom Fields</button> --}}
                              <hr>
                              <h6 class="text-muted small">ACTIONS</h6>
                              <button class="btn btn-light btn-action" id="modalMoveTaskBtn"><i class="fas fa-arrow-right mr-1"></i> Move</button>
                              {{-- <button class="btn btn-light btn-action">Copy</button> --}}
                              {{-- <button class="btn btn-light btn-action">Mirror</button> --}}
                              {{-- <button class="btn btn-light btn-action">Make template</button> --}}
                              <button class="btn btn-light btn-action" id="modalArchiveTaskBtn"><i class="fas fa-archive mr-1"></i> Archive</button>
                              <button type="button" class="btn btn-danger btn-action" id="deleteTaskBtn"><i class="fas fa-trash-alt mr-1"></i> Delete Task</button>
                              {{-- Nút Save Changes (đã có ở footer cũ, giờ là Save Description) --}}
                          </div>
                      </div>
                  </div>
              </div>
              {{-- Có thể thêm modal-footer nếu muốn các nút đóng/lưu cố định ở dưới --}}
              {{-- <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div> --}}
          </div>
      </div>
  </div>
</body>
</html>