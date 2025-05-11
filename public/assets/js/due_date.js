$(document).ready(function () {
    // Khởi tạo jQuery UI datepicker
    $('#modalDueDateInput').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0 // không cho chọn ngày quá khứ
    });

    // Hiển thị input datepicker khi click nút
    $(document).on('click', '#modalSetDueDateTrigger', function (e) {
        e.preventDefault();
        $('#dueDatePickerContainer').show();
        $('#modalDueDateInput').val(
            originalTaskData.due_date || ''
        ).focus();
    });

    // Hủy chọn date
    $(document).on('click', '#modalCancelDueDateBtn', function () {
        $('#dueDatePickerContainer').hide();
        $('#modalDueDateInput').val('');
    });

    $(document).on('click', '#modalSaveDueDateBtn', function () {
        const taskId = $('#modalTaskId').val();
        const newDueDate = $('#modalDueDateInput').val().trim();
        const $btn = $(this);

        if (!newDueDate) {
            return alert('Vui lòng chọn ngày hết hạn.');
        }


    });

});
