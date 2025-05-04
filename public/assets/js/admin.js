$(document).ready(function () {
    let isDragging = false;
    let offsetX = 0, offsetY = 0;

    let $sheepWrapper = $(".sheep-wrapper");
    let originalPosition = {
        top: $sheepWrapper.css("top"),
        left: $sheepWrapper.css("left")
    };

    // Cho kéo con cừu
    $(".sheep-wrapper").draggable({
        containment: "window",
        start: function (event, ui) {
            isDragging = true;
            offsetX = event.pageX - ui.position.left;
            offsetY = event.pageY - ui.position.top;
        },
        stop: function (event, ui) {
            isDragging = false;
            $sheepWrapper.css("cursor", "grab");

            // Kiểm tra va chạm với card
            $(".card-drop-target").each(function () {
                let $card = $(this);
                let offset = $card.offset();
                let width = $card.outerWidth();
                let height = $card.outerHeight();

                if (
                    ui.offset.left >= offset.left &&
                    ui.offset.left <= offset.left + width &&
                    ui.offset.top >= offset.top &&
                    ui.offset.top <= offset.top + height
                ) {
                    // Trúng card
                    showOverlayAndHideSheep();
                }
            });
        }
    });

    function showOverlayAndHideSheep() {
        $sheepWrapper.hide();
        $("#cardOverlay").fadeIn();
    }

    $("#closeOverlay").on("click", function () {
        $("#cardOverlay").fadeOut(function () {
            // Trả cừu về chỗ cũ
            $sheepWrapper.css({
                top: originalPosition.top,
                left: originalPosition.left
            }).show();
        });
    });

    // Click để quay
    $(".sheep-wrapper").on("click", function () {
        const $sheep = $(this).find(".sheep");
        $sheep.stop(true, true).css("transform", "rotate(0deg)");

        $({ deg: 0 }).animate({ deg: 360 }, {
            duration: 1000,
            step: function (now) {
                $sheep.css("transform", "rotate(" + now + "deg)");
            },
            complete: function () {
                $sheep.css("transform", "none");
                $sheep.find(".mouth").addClass("yawn");
                setTimeout(() => $sheep.find(".mouth").removeClass("yawn"), 1000);
            }
        });
    });

    // Tự quay mỗi 10s
    setInterval(function () {
        const $sheep = $(".sheep-wrapper .sheep");
        $sheep.stop(true, true).css("transform", "rotate(0deg)");

        $({ deg: 0 }).animate({ deg: 360 }, {
            duration: 1000,
            step: function (now) {
                $sheep.css("transform", "rotate(" + now + "deg)");
            },
            complete: function () {
                $sheep.css("transform", "none");
                $sheep.find(".mouth").addClass("yawn");
                setTimeout(() => $sheep.find(".mouth").removeClass("yawn"), 1000);
            }
        });
    }, 10000);

    // Tạo nội dung overlay
    $("#cardOverlay")
    .hide() // đảm bảo vẫn ẩn
    .html(`
        <div class="overlay-content">
            <p>🎉 Cừu đã chui vào thẻ thành công!</p>
            <button class="overlay-close-btn">Đóng</button>
        </div>
    `);


    // Xử lý đóng
    $("#cardOverlay").on("click", ".overlay-close-btn", function () {
        $("#cardOverlay").fadeOut(function () {
            // Hiện lại cừu nếu cần
            $(".sheep-wrapper").css({
                top: "10%", // hoặc original position nếu có lưu
                left: "65%"
            }).show();
        });
    });
});

