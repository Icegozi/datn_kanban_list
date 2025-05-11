// public/assets/js/attachment.js

var AttachmentManager = (function ($) {
    let stagedFiles = []; // Giữ các file người dùng chọn trước khi tải lên
    let currentTaskIdForAttachment = null; // ID của task hiện tại đang mở trong modal

    // --- Helper Functions (Có thể dùng chung với TaskJS hoặc tạo file utils riêng) ---
    function getRoute(routeName, params = {}) {
        if (typeof window.routeUrls === 'undefined' || !window.routeUrls) {
            console.error("AttachmentManager Error: window.routeUrls is not defined!");
            return '#ROUTE_ERROR';
        }
        let url = window.routeUrls[routeName] || '';
        if (!url) {
            console.error(`AttachmentManager Error: Route "${routeName}" not found in window.routeUrls.`);
            return '#ROUTE_NOT_FOUND';
        }
        for (const key in params) {
            const placeholder = `:${key}Placeholder`; // e.g., :taskIdPlaceholder
            const placeholderSimple = `:${key}`;     // e.g., :taskId
            url = url.replace(new RegExp(placeholder.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), params[key]);
            url = url.replace(new RegExp(placeholderSimple.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), params[key]);
        }
        if ((url.includes(':boardIdPlaceholder') || url.includes(':boardId')) && !params['boardId'] && window.currentBoardId) {
            url = url.replace(/:boardId(Placeholder)?/g, window.currentBoardId);
        }
        return url;
    }

    function showNotification(message, type = 'success') {
        // Đơn giản hóa bằng alert, bạn có thể thay thế bằng thư viện notification xịn hơn
        alert((type === 'error' ? 'Lỗi: ' : 'Thành công: ') + message);
        console.log(type.toUpperCase() + ": " + message);
    }
    // --- End Helper Functions ---

    let $fileInput; // Biến giữ đối tượng input file ẩn

    // Đảm bảo input file tồn tại và khởi tạo sự kiện cho nó
    function ensureFileInput() {
        if (!$fileInput || $fileInput.length === 0) {
            $fileInput = $('#taskAttachmentFileInput');
            if ($fileInput.length === 0) { // Nếu chưa có, tạo mới
                $fileInput = $('<input type="file" id="taskAttachmentFileInput" multiple style="display: none;">');
                $('body').append($fileInput); // Thêm vào body để có thể hoạt động
            }
            // Gắn sự kiện 'change' chỉ một lần
            $fileInput.off('change.attachmentManager').on('change.attachmentManager', handleFileSelection);
        }
        return $fileInput;
    }

    // Xử lý khi người dùng chọn file
    function handleFileSelection(event) {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        const $attachmentsContainer = $('#modalTaskAttachments');
        // Xóa thông báo "Chưa có đính kèm" nếu là lần đầu thêm file
        if ($attachmentsContainer.find('.no-attachments-message').length > 0) {
            $attachmentsContainer.empty();
        }

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            // Kiểm tra trùng lặp trong stagedFiles trước khi thêm
            if (!stagedFiles.find(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                stagedFiles.push(file);
                renderStagedFile(file, $attachmentsContainer);
            }
        }

        // Hiển thị nút "Tải lên" nếu có file trong danh sách chờ
        if (stagedFiles.length > 0 && $('#uploadNewAttachmentsBtn').length === 0) {
            const $uploadBtn = $('<button id="uploadNewAttachmentsBtn" class="btn btn-sm btn-primary mt-2 mb-2"><i class="fas fa-upload mr-1"></i>Tải lên các tệp đã chọn</button>');
            $attachmentsContainer.append($uploadBtn);
        }

        // Reset input file để cho phép chọn lại cùng file nếu đã xóa khỏi staged list
        $(this).val('');
    }

    // Hiển thị file đang chờ tải lên (staged)
    function renderStagedFile(file, container) {
        const uniqueId = 'staged-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const fileSizeKB = (file.size / 1024).toFixed(1);
        const $fileElement = $(`
            <div class="attachment-item mb-2 p-2 border rounded bg-light" data-staged-id="${uniqueId}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file-alt mr-2 text-secondary"></i>
                        <span class="attachment-name font-weight-bold">${file.name}</span>
                        <small class="text-muted ml-2">(${fileSizeKB} KB) - Chờ tải lên</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-staged-attachment-btn" title="Xóa khỏi danh sách chờ">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);

        $fileElement.find('.remove-staged-attachment-btn').on('click', function() {
            stagedFiles = stagedFiles.filter(f => !(f.name === file.name && f.size === file.size && f.lastModified === file.lastModified));
            $fileElement.remove();
            if (stagedFiles.length === 0) {
                $('#uploadNewAttachmentsBtn').remove();
                if (container.find('.attachment-item').length === 0) { // Nếu không còn file nào (cả staged và existing)
                    container.html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                }
            }
        });
        // Thêm vào đầu danh sách hoặc trước nút Upload nếu có
        const $uploadBtn = container.find('#uploadNewAttachmentsBtn');
        if ($uploadBtn.length > 0) {
            $fileElement.insertBefore($uploadBtn);
        } else {
            container.append($fileElement);
        }
    }

    // Hiển thị file đã có trên server
    function renderExistingAttachment(attachment, container) {
        const fileSizeKB = attachment.formatted_capacity; // Sử dụng accessor mới nếu bạn đã đổi tên, hoặc tính toán lại
        const fileIcon = attachment.icon_class; // Lấy từ accessor
        const downloadUrl = `/attachments/${attachment.id}/download`; 

        const $fileElement = $(`
            <div class="attachment-item mb-2 p-2 border rounded" data-attachment-id="${attachment.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="${fileIcon} mr-2"></i>
                        <a href="${downloadUrl}" class="attachment-name font-weight-bold" title="Tải xuống ${attachment.file_name}">${attachment.file_name}</a>
                            ${fileSizeKB ? `<small class="text-muted ml-2">(${fileSizeKB})</small>` : ''}
                            ${attachment.uploaded_at_formatted ? `<small class="text-muted ml-2"> - ${attachment.uploaded_at_formatted}</small>` : ''}
                        </div>
                        <button class="btn btn-sm btn-outline-danger delete-existing-attachment-btn" title="Xóa tệp đính kèm này">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `);

            $fileElement.find('.delete-existing-attachment-btn').on('click', function() {
                deleteAttachmentFromServer(attachment.id, $fileElement);
            });
            container.append($fileElement);
    }

    function getFileIconClass(fileName) {
        const extension = fileName.split('.').pop().toLowerCase();
        switch (extension) {
            case 'pdf': return 'fas fa-file-pdf text-danger';
            case 'doc': case 'docx': return 'fas fa-file-word text-primary';
            case 'xls': case 'xlsx': return 'fas fa-file-excel text-success';
            case 'ppt': case 'pptx': return 'fas fa-file-powerpoint text-warning';
            case 'zip': case 'rar': return 'fas fa-file-archive text-info';
            case 'txt': return 'fas fa-file-alt text-secondary';
            case 'jpg': case 'jpeg': case 'png': case 'gif': return 'fas fa-file-image text-info';
            default: return 'fas fa-file text-muted';
        }
    }


    // Tải và hiển thị danh sách các file đính kèm đã có
    function loadAndRenderExistingAttachments(taskId) {
        setCurrentTaskId(taskId); // Cập nhật ID task hiện tại
        stagedFiles = []; // Xóa các file đang chờ tải lên (nếu có từ lần mở modal trước)
        $('#uploadNewAttachmentsBtn').remove(); // Xóa nút tải lên (nếu có)

        const $attachmentsContainer = $('#modalTaskAttachments');
        $attachmentsContainer.html('<p class="text-muted small"><i class="fas fa-spinner fa-spin"></i> Đang tải đính kèm...</p>');

        const url = getRoute('attachmentsIndexBase', { taskIdPlaceholder: currentTaskIdForAttachment });
        if (url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route lấy danh sách đính kèm.', 'error');
            $attachmentsContainer.html('<p class="text-danger small no-attachments-message">Lỗi tải đính kèm (route).</p>');
            return;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $attachmentsContainer.empty(); // Xóa nội dung cũ (loading message)
                if (response.success && response.attachments && response.attachments.length > 0) {
                    response.attachments.forEach(attachment => {
                        renderExistingAttachment(attachment, $attachmentsContainer);
                    });
                } else if (response.success) {
                    $attachmentsContainer.html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                } else {
                    $attachmentsContainer.html('<p class="text-danger small no-attachments-message">Không thể tải danh sách đính kèm.</p>');
                    showNotification(response.message || 'Không thể tải danh sách đính kèm.', 'error');
                }
            },
            error: function(jqXHR) {
                $attachmentsContainer.html('<p class="text-danger small no-attachments-message">Lỗi máy chủ khi tải đính kèm.</p>');
                showNotification('Lỗi máy chủ khi tải đính kèm: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
            }
        });
    }

    // Tải các file đã chọn (stagedFiles) lên server
    function uploadStagedFiles() {
        if (stagedFiles.length === 0) {
            showNotification('Không có tệp nào được chọn để tải lên.', 'info');
            return;
        }
        if (!currentTaskIdForAttachment) {
            showNotification('Không xác định được ID công việc. Không thể tải tệp lên.', 'error');
            console.error("AttachmentManager: currentTaskIdForAttachment is not set.");
            return;
        }

        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // CSRF Token
        stagedFiles.forEach((file) => {
            formData.append('attachments[]', file, file.name); // Gửi dưới dạng mảng 'attachments[]'
        });

        const $uploadBtn = $('#uploadNewAttachmentsBtn');
        $uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Đang tải lên...');

        const url = getRoute('attachmentsStoreBase', { taskIdPlaceholder: currentTaskIdForAttachment });
         if (url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route tải lên đính kèm.', 'error');
            $uploadBtn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Tải lên các tệp đã chọn');
            return;
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            contentType: false, // Quan trọng: không set contentType header
            processData: false, // Quan trọng: không xử lý data
            success: function(response) {
                if (response.success) {
                    showNotification(response.message || 'Tải lên tệp đính kèm thành công!', 'success');
                    // Xóa các file đã staged và UI liên quan
                    $('#modalTaskAttachments .attachment-item[data-staged-id]').remove();
                    stagedFiles = [];
                    $uploadBtn.remove();

                    // Tải lại toàn bộ danh sách đính kèm để hiển thị file mới (hoặc chỉ thêm file mới nếu API trả về thông tin file vừa upload)
                    // Đơn giản nhất là tải lại toàn bộ:
                    loadAndRenderExistingAttachments(currentTaskIdForAttachment);
                } else {
                    showNotification(response.message || 'Lỗi khi tải lên tệp.', 'error');
                }
            },
            error: function(jqXHR) {
                let errorMsg = 'Lỗi AJAX khi tải lên: ';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors['attachments.0']) {
                     errorMsg += jqXHR.responseJSON.errors['attachments.0'].join(', '); // Hiển thị lỗi validation cụ thể nếu có
                } else {
                    errorMsg += (jqXHR.responseJSON?.message || jqXHR.statusText);
                }
                showNotification(errorMsg, 'error');
            },
            complete: function() {
                // Đảm bảo nút upload được kích hoạt lại nếu nó vẫn còn trên DOM (ví dụ: nếu upload lỗi)
                const $btn = $('#uploadNewAttachmentsBtn');
                if ($btn.length > 0) {
                    $btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Tải lên các tệp đã chọn');
                }
                // Nếu sau khi upload, không còn file nào (cả staged và existing), hiển thị lại thông báo
                if ($('#modalTaskAttachments .attachment-item').length === 0 && $('#uploadNewAttachmentsBtn').length === 0) {
                    $('#modalTaskAttachments').html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                }
            }
        });
    }

    // Xóa file đính kèm đã có trên server
    function deleteAttachmentFromServer(attachmentId, $elementToRemove) {
        if (!confirm('Bạn có chắc chắn muốn xóa tệp đính kèm này không?')) {
            return;
        }

        const url = getRoute('attachmentsDestroyBase', { attachmentIdPlaceholder: attachmentId });
        if (url.startsWith('#ROUTE_')) {
            showNotification('Lỗi cấu hình route xóa đính kèm.', 'error');
            return;
        }

        const $deleteButton = $elementToRemove.find('.delete-existing-attachment-btn');
        $deleteButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>'); // Hiệu ứng loading

        $.ajax({
            url: url,
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') }, // CSRF Token
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message || 'Đã xóa tệp đính kèm.', 'success');
                    $elementToRemove.fadeOut(300, function() {
                        $(this).remove();
                        // Nếu không còn file nào, hiển thị thông báo
                        if ($('#modalTaskAttachments .attachment-item').length === 0) {
                            $('#modalTaskAttachments').html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                        }
                    });
                } else {
                    showNotification(response.message || 'Lỗi khi xóa tệp đính kèm.', 'error');
                    $deleteButton.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>'); // Reset nút
                }
            },
            error: function(jqXHR) {
                showNotification('Lỗi AJAX khi xóa: ' + (jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                $deleteButton.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>'); // Reset nút
            }
        });
    }

    // Khởi tạo các listener
    function init() {
        ensureFileInput(); // Tạo input file ẩn nếu chưa có

        // Sự kiện click vào nút "Đính kèm" trong modal
        $(document).on('click', '#modalAddAttachmentTrigger', function(e) {
            e.preventDefault();
            // Lấy task ID từ modal nếu chưa có (quan trọng để biết upload cho task nào)
            if (!currentTaskIdForAttachment) {
                const modalTaskId = $('#modalTaskId').val();
                if (modalTaskId) {
                    setCurrentTaskId(modalTaskId);
                } else {
                    showNotification("Không tìm thấy ID công việc. Hãy đảm bảo công việc đã được lưu.", "warning");
                    console.warn("AttachmentManager: Cannot trigger file input, currentTaskIdForAttachment is null and modalTaskId is not found.");
                    return;
                }
            }
            $fileInput.click(); // Kích hoạt input file ẩn
        });

        // Sự kiện click vào nút "Tải lên các tệp đã chọn" (nút này được tạo động)
        $(document).on('click', '#uploadNewAttachmentsBtn', function() {
            uploadStagedFiles();
        });

        // Reset trạng thái khi modal đóng (quan trọng để không bị rò rỉ dữ liệu giữa các lần mở modal)
        $('#taskDetailModal').on('hidden.bs.modal', function () {
            stagedFiles = [];
            if ($fileInput) {
                 $fileInput.val(''); // Xóa lựa chọn file cũ trong input
            }
            $('#modalTaskAttachments').html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>'); // Reset view
            currentTaskIdForAttachment = null; // Reset ID task
        });

        console.log("AttachmentManager initialized.");
    }

    // Hàm public để TaskJS có thể gọi
    function setCurrentTaskId(taskId) {
        currentTaskIdForAttachment = taskId;
    }

    // Public API của module
    return {
        init: init,
        loadAttachments: loadAndRenderExistingAttachments, // Để TaskJS gọi khi mở modal
        setCurrentTaskId: setCurrentTaskId // Để TaskJS cập nhật Task ID khi cần
    };

})(jQuery);

// Khởi tạo AttachmentManager khi tài liệu sẵn sàng
$(document).ready(function() {
    if (typeof AttachmentManager !== 'undefined' && AttachmentManager.init) {
        AttachmentManager.init();
    } else {
        console.error("AttachmentManager is not defined or its init method is missing.");
    }
});