// --- Helper function defined globally within this script tag ---
function createBoardCardHtml(board) {
    // Note: Using template literals for easier HTML construction
    return `
            <div class="col-md-4 col-lg-3 mb-4 card-drop-target board-card" id="board-card-${board.id}">
                <div class="card shadow-sm h-100 card-hover">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h6 class="mb-0 text-truncate font-weight-bold board-name">${board.name}</h6>
                            <div class="dropdown">
                                <a href="#" class="text-muted dropdown-toggle-no-caret"
                                    id="itemMenu${board.id}" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false" aria-label="Tùy chọn bảng">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="itemMenu${board.id}">
                                    <a class="dropdown-item open-board-link" href="${board.url_show}">
                                        <i class="fas fa-folder-open fa-fw mr-2 text-muted"></i>Mở
                                    </a>
                                    <a class="dropdown-item rename-board-link" href="#" data-id="${board.id}" data-name="${board.name}" data-update-url="${board.url_update}">
                                        <i class="fas fa-pencil-alt fa-fw mr-2 text-muted"></i>Sửa tên
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete-board-link text-danger" href="#" data-id="${board.id}" data-name="${board.name}" data-destroy-url="${board.url_destroy}">
                                        <i class="fas fa-trash-alt fa-fw mr-2"></i> Xoá
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center text-muted small mt-auto board-timestamp">
                            <i class="far fa-clock fa-fw mr-2"></i>
                            <span>${board.updated_at_formatted || board.created_at_formatted}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
}

$(document).ready(function () {

    // CSRF Token Setup for AJAX (Important!) 
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 1. create New Board
    $('#add-board-btn').on('click', function () {
        const boardName = prompt("Nhập tên bảng mới:", "");

        if (boardName && boardName.trim() !== "") {
            $.ajax({
                url: window.routeUrls.boardsStore, 
                method: 'POST',
                data: {
                    name: boardName.trim()
                },
                dataType: 'json',
                success: function (response) {
                    console.log("Create Success Response:", response); 
                    if (response.success && response.board) {
                        const newBoardHtml = createBoardCardHtml(response.board);

                        $('#no-boards-message').remove();
                        $('#board-list-container').prepend(newBoardHtml);
                        // Re-enable dropdowns for the new card if needed (usually automatic with Bootstrap)
                        // $('.dropdown-toggle').dropdown(); // Might not be necessary

                        alert(response.message);
                    } else {
                        alert(response.message || 'Không thể tạo bảng. Phản hồi không hợp lệ.');
                    }
                },
                error: function (xhr) {
                    console.error("Create Error XHR:", xhr); // Debug log
                    let errorMessage = 'Đã xảy ra lỗi khi tạo bảng.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors && xhr.responseJSON.errors.name) {
                            errorMessage = xhr.responseJSON.errors.name[0];
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    } else {
                        errorMessage = `Lỗi không xác định (HTTP ${xhr.status}: ${xhr.statusText})`;
                    }
                    console.error("Error response Text:", xhr.responseText);
                    alert(errorMessage);
                }
            });
        } else if (boardName !== null) {
            alert("Tên bảng không được để trống.");
        }
    });

    // 2. rename Board (Modal Trigger) 
    $('#board-list-container').on('click', '.rename-board-link', function (e) {
        e.preventDefault();
        const boardId = $(this).data('id');
        const currentName = $(this).data('name');
        const updateUrl = $(this).data('update-url');

        $('#renameBoardModal').find('#rename-board-id').val(boardId);
        $('#renameBoardModal').find('#rename-board-current-name').val(currentName);
        $('#renameBoardModal').find('#rename-board-new-name').val(currentName);
        $('#renameBoardModal').find('#rename-board-form').attr('action', updateUrl);
        $('#renameBoardModal').modal('show');
        // Optional: Add focus logic after modal is shown
        $('#renameBoardModal').on('shown.bs.modal', function () {
            $('#rename-board-new-name', this).focus().select();
        }).on('hidden.bs.modal', function () {
            // Important: Remove the event listener once hidden to prevent multiple bindings
            $(this).off('shown.bs.modal');
        });
    });

    // --- RENAME Board (Modal Form Submission) ---
    $('#rename-board-form').on('submit', function (e) {
        e.preventDefault();
        const boardId = $('#rename-board-id').val();
        const newName = $('#rename-board-new-name').val().trim();
        const currentName = $('#rename-board-current-name').val();
        const updateUrl = $(this).attr('action');

        if (newName && newName !== currentName) {
            performRenameAjax(boardId, newName, updateUrl);
        } else if (!newName) {
            alert("Tên bảng không được để trống.");
            // Consider showing error within the modal instead of alert
        } else {
            $('#renameBoardModal').modal('hide'); 
        }
    });

    // --- RENAME Board (AJAX Call) ---
    function performRenameAjax(boardId, newName, updateUrl) {
        $.ajax({
            url: updateUrl,
            method: 'PUT',
            data: { name: newName },
            dataType: 'json',
            success: function (response) {
                console.log("Update Success Response:", response); // Debug log
                if (response.success) {
                    const $card = $(`#board-card-${boardId}`);
                    $card.find('.board-name').text(response.new_name);
                    $card.find('.rename-board-link').data('name', response.new_name); // Update data attribute
                    $card.find('.delete-board-link').data('name', response.new_name); // Update data attribute for delete confirmation

                    if (response.updated_at_formatted) {
                        $card.find('.board-timestamp span').text(response.updated_at_formatted);
                    }
                    $('#renameBoardModal').modal('hide');
                    // alert(response.message); //fix bug
                } else {
                    alert(response.message || 'Không thể đổi tên bảng.');
                    // Consider showing error within the modal
                }
            },
            error: function (xhr) {
                console.error("Update Error XHR:", xhr); // Debug log
                let errorMessage = 'Đã xảy ra lỗi khi đổi tên bảng.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.name) {
                        errorMessage = xhr.responseJSON.errors.name[0];
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else {
                    errorMessage = `Lỗi không xác định (HTTP ${xhr.status}: ${xhr.statusText})`;
                }
                console.error("Error response Text:", xhr.responseText);
                alert(errorMessage);
                // Consider showing error within the modal
            }
        });
    }

    // --- 3. DELETE Board ---
    $('#board-list-container').on('click', '.delete-board-link', function (e) {
        e.preventDefault();
        const boardId = $(this).data('id');
        const boardName = $(this).data('name');
        const destroyUrl = $(this).data('destroy-url');

        if (confirm(`Bạn có chắc chắn muốn xoá bảng "${boardName}" không? Hành động này không thể hoàn tác.`)) {
            $.ajax({
                url: destroyUrl,
                method: 'DELETE',
                dataType: 'json',
                success: function (response) {
                    console.log("Delete Success Response:", response); // Debug log
                    if (response.success) {
                        $(`#board-card-${boardId}`).remove();
                        if ($('#board-list-container .board-card').length === 0) {
                            $('#board-list-container').html(`
                                     <div class="col-12" id="no-boards-message">
                                         <p class="text-muted text-center mt-5">Bạn chưa có bảng làm việc nào. Hãy tạo một bảng mới!</p>
                                     </div>
                                 `);
                        }
                        alert(response.message);
                    } else {
                        alert(response.message || 'Không thể xoá bảng.');
                    }
                },
                error: function (xhr) {
                    console.error("Delete Error XHR:", xhr); // Debug log
                    let errorMessage = 'Đã xảy ra lỗi khi xoá bảng.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else {
                        errorMessage = `Lỗi không xác định (HTTP ${xhr.status}: ${xhr.statusText})`;
                    }
                    console.error("Error response Text:", xhr.responseText);
                    alert(errorMessage);
                }
            });
        }
    });


    // ===========================================
    // ========== SHEEP/OVERLAY LOGIC ============
    // ===========================================
    let isDragging = false;
    let offsetX = 0, offsetY = 0;
    let ovlTop = '40%';
    let ovlLeft = '40%';
    let $sheepWrapper = $(".sheep-wrapper"); // Make sure '.sheep-wrapper' exists in your HTML

    if ($sheepWrapper.length) { // Only run sheep logic if the element exists
        let originalPosition = {
            top: $sheepWrapper.css("top"),
            left: $sheepWrapper.css("left")
        };

        // Ensure jQuery UI is loaded before calling draggable
        if (typeof $.fn.draggable === 'function') {
            $sheepWrapper.draggable({
                containment: "window",
                cursor: "grabbing", // Change cursor while dragging
                start: function (event, ui) {
                    isDragging = true;
                    // Offset calculation might not be needed depending on use case
                    // offsetX = event.pageX - ui.position.left;
                    // offsetY = event.pageY - ui.position.top;
                },
                stop: function (event, ui) {
                    isDragging = false;
                    $sheepWrapper.css("cursor", "grab"); // Reset cursor

                    // Use ui.helper which refers to the element being dragged
                    let sheepOffset = ui.helper.offset();
                    let sheepWidth = ui.helper.outerWidth();
                    let sheepHeight = ui.helper.outerHeight();

                    // Check collision with dynamically added cards too
                    $("#board-list-container .card-drop-target").each(function () {
                        let $card = $(this);
                        let cardOffset = $card.offset();
                        let cardWidth = $card.outerWidth();
                        let cardHeight = $card.outerHeight();

                        // Basic AABB collision detection
                        let collision = !(
                            sheepOffset.left + sheepWidth < cardOffset.left ||
                            sheepOffset.left > cardOffset.left + cardWidth ||
                            sheepOffset.top + sheepHeight < cardOffset.top ||
                            sheepOffset.top > cardOffset.top + cardHeight
                        );

                        if (collision) {
                            console.log("Sheep collided with card:", $card.find('.board-name').text());
                            showOverlayAndHideSheep();
                            return false; // Exit .each loop once collision is found
                        }
                    });
                }
            });
        } else {
            console.warn("jQuery UI Draggable not loaded. Sheep cannot be dragged.");
        }


        function showOverlayAndHideSheep() {
            $sheepWrapper.hide();
            // Ensure #cardOverlay exists and has content before fading in
            if ($("#cardOverlay").length === 0) {
                // Create overlay if it doesn't exist
                $('body').append('<div id="cardOverlay" style="display: none;"></div>'); // Add basic overlay div
                $("#cardOverlay").html(`
                        <div class="overlay-content" id="ovl_content">
                            <p>🎉 Cừu đã chui vào thẻ thành công!</p>
                            <button class="btn btn-primary overlay-close-btn">Đóng</button> 
                        </div>
                    `);
            } else if ($("#cardOverlay .overlay-content").length === 0) {
                // Add content if overlay exists but is empty
                $("#cardOverlay").html(`
                        <div class="overlay-content" id="ovl_content">
                            <p>🎉 Cừu đã chui vào thẻ thành công!</p>
                            <button class="btn btn-primary overlay-close-btn">Đóng</button>
                        </div>
                    `);
            }
            $("#ovl_content").css({
                position: "absolute", // thêm dòng này
                top: ovlTop,
                left: ovlLeft
            });
            
            $("#cardOverlay").fadeIn();
        }

        // Use event delegation for the close button in case overlay is created dynamically
        $(document).on("click", "#cardOverlay .overlay-close-btn", function () {
            $("#cardOverlay").fadeOut(function () {
                $sheepWrapper.css({
                    top: originalPosition.top,
                    left: originalPosition.left
                }).show();
            });
        });

        // Click sheep to spin
        $sheepWrapper.on("click", function () {
            if (isDragging) return; // Don't spin if dragging just finished

            const $sheep = $(this).find(".sheep"); // Make sure '.sheep' exists inside '.sheep-wrapper'
            if ($sheep.length === 0) return;

            $sheep.stop(true, true).css("transform", "rotate(0deg)");

            $({ deg: 0 }).animate({ deg: 360 }, {
                duration: 600, // Faster spin?
                easing: 'linear', // Consistent spin speed
                step: function (now) {
                    $sheep.css("transform", "rotate(" + now + "deg)");
                },
                complete: function () {
                    $sheep.css("transform", "none"); // Reset transform
                    // Optional: Yawn animation if elements exist
                    const $mouth = $sheep.find(".mouth");
                    if ($mouth.length) {
                        $mouth.addClass("yawn");
                        setTimeout(() => $mouth.removeClass("yawn"), 800);
                    }
                }
            });
        });

        // Spin interval - ensure elements exist
        const spinInterval = setInterval(function () {
            const $sheepForInterval = $(".sheep-wrapper .sheep");
            if ($sheepForInterval.length === 0) {
                // Optionally clear interval if sheep is removed from DOM
                // clearInterval(spinInterval);
                return;
            }

            // Check if sheep is currently being animated or hidden
            if ($sheepForInterval.is(':animated') || !$sheepWrapper.is(':visible')) {
                return;
            }

            $sheepForInterval.stop(true, true).css("transform", "rotate(0deg)");
            $({ deg: 0 }).animate({ deg: 360 }, {
                duration: 1000,
                easing: 'linear',
                step: function (now) {
                    $sheepForInterval.css("transform", "rotate(" + now + "deg)");
                },
                complete: function () {
                    $sheepForInterval.css("transform", "none");
                    const $mouthForInterval = $sheepForInterval.find(".mouth");
                    if ($mouthForInterval.length) {
                        $mouthForInterval.addClass("yawn");
                        setTimeout(() => $mouthForInterval.removeClass("yawn"), 1000);
                    }
                }
            });
        }, 10000); // 10 seconds

    } else {
        console.log("Sheep wrapper element not found. Sheep logic skipped.");
    }

}); // <-- End of the single $(document).ready()
