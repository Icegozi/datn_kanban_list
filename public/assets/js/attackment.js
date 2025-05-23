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
        alert(message);
        console.log(type.toUpperCase() + ": " + message);
    }

    let $fileInput; 

    function ensureFileInput() {
        if (!$fileInput || $fileInput.length === 0) {
            $fileInput = $('#taskAttachmentFileInput');
            if ($fileInput.length === 0) { 
                $fileInput = $('<input type="file" id="taskAttachmentFileInput" multiple style="display: none;">');
                $('body').append($fileInput);
            }
            $fileInput.off('change.attachmentManager').on('change.attachmentManager', handleFileSelection);
        }
        return $fileInput;
    }

    function handleFileSelection(event) {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        const $attachmentsContainer = $('#modalTaskAttachments');
        if ($attachmentsContainer.find('.no-attachments-message').length > 0) {
            $attachmentsContainer.empty();
        }

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            if (!stagedFiles.find(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                stagedFiles.push(file);
                renderStagedFile(file, $attachmentsContainer);
            }
        }

        if (stagedFiles.length > 0 && $('#uploadNewAttachmentsBtn').length === 0) {
            const $uploadBtn = $('<button id="uploadNewAttachmentsBtn" class="btn btn-sm btn-primary mt-2 mb-2"><i class="fas fa-upload mr-1"></i>Tải lên các tệp đã chọn</button>');
            $attachmentsContainer.append($uploadBtn);
        }

        $(this).val('');
    }

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
                if (container.find('.attachment-item').length === 0) {
                    container.html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                }
            }
        });

        const $uploadBtn = container.find('#uploadNewAttachmentsBtn');
        if ($uploadBtn.length > 0) {
            $fileElement.insertBefore($uploadBtn);
        } else {
            container.append($fileElement);
        }
    }

    function renderExistingAttachment(attachment, container) {
        const fileSizeKB = attachment.formatted_capacity; 
        const fileIcon = attachment.icon_class; 
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


    function loadAndRenderExistingAttachments(taskId) {
        setCurrentTaskId(taskId); 
        stagedFiles = []; 
        $('#uploadNewAttachmentsBtn').remove(); 

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
        formData.append('_token', $('meta[name="csrf-token"]').attr('content')); 
        stagedFiles.forEach((file) => {
            formData.append('attachments[]', file, file.name); 
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
            contentType: false, 
            processData: false, 
            success: function(response) {
                if (response.success) {
                    showNotification(response.message || 'Tải lên tệp đính kèm thành công!', 'success');
                    $('#modalTaskAttachments .attachment-item[data-staged-id]').remove();
                    stagedFiles = [];
                    $uploadBtn.remove();
                    loadAndRenderExistingAttachments(currentTaskIdForAttachment);
                } else {
                    showNotification(response.message || 'Lỗi khi tải lên tệp.', 'error');
                }
            },
            error: function(jqXHR) {
                let errorMsg = '';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors['attachments.0']) {
                     errorMsg += jqXHR.responseJSON.errors['attachments.0'].join(', '); 
                } else {
                    errorMsg += (jqXHR.responseJSON?.message || jqXHR.statusText);
                }
                showNotification(errorMsg, 'error');
            },
            complete: function() {
                const $btn = $('#uploadNewAttachmentsBtn');
                if ($btn.length > 0) {
                    $btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Tải lên các tệp đã chọn');
                }
                if ($('#modalTaskAttachments .attachment-item').length === 0 && $('#uploadNewAttachmentsBtn').length === 0) {
                    $('#modalTaskAttachments').html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                }
            }
        });
    }

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
        $deleteButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>'); 

        $.ajax({
            url: url,
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') }, 
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message || 'Đã xóa tệp đính kèm.', 'success');
                    $elementToRemove.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#modalTaskAttachments .attachment-item').length === 0) {
                            $('#modalTaskAttachments').html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>');
                        }
                    });
                } else {
                    showNotification(response.message || 'Lỗi khi xóa tệp đính kèm.', 'error');
                    $deleteButton.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>'); 
                }
            },
            error: function(jqXHR) {
                showNotification((jqXHR.responseJSON?.message || jqXHR.statusText), 'error');
                $deleteButton.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>'); 
            }
        });
    }

    function init() {
        ensureFileInput(); 

        $(document).on('click', '#modalAddAttachmentTrigger', function(e) {
            e.preventDefault();
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
            $fileInput.click(); 
        });

        $(document).on('click', '#uploadNewAttachmentsBtn', function() {
            uploadStagedFiles();
        });

        $('#taskDetailModal').on('hidden.bs.modal', function () {
            stagedFiles = [];
            if ($fileInput) {
                 $fileInput.val('');
            }
            $('#modalTaskAttachments').html('<p class="text-muted small no-attachments-message">Chưa có đính kèm.</p>'); // Reset view
            currentTaskIdForAttachment = null; 
        });

        console.log("AttachmentManager initialized.");
    }

    function setCurrentTaskId(taskId) {
        currentTaskIdForAttachment = taskId;
    }

    return {
        init: init,
        loadAttachments: loadAndRenderExistingAttachments, 
        setCurrentTaskId: setCurrentTaskId 
    };

})(jQuery);

$(document).ready(function() {
    if (typeof AttachmentManager !== 'undefined' && AttachmentManager.init) {
        AttachmentManager.init();
    } else {
        console.error("AttachmentManager is not defined or its init method is missing.");
    }
});