$('button.smsOpenImportModal').click(function () {
    var is_select = $('#mobileSelect').attr('con');

    $(".ajax_succ_div,.ajax_err_div").hide();
    $(".ajax_res_err,.ajax_res_succ").empty();

    if (is_select == "true") {
        var ans = confirm("Are you sure you want to import a new data? Your imported data will be cleared");
        if (ans == true) {
            $('.mobileSelectOptions').remove();
            $('.smsModal').show();
            $('.e_mobile').hide();

            // $('#mobileSelect').attr('con', 'false');
        } else {
            return false;
        }
    } else {
        $('.smsModal').show();
    }
});

$('button.smsCloseImportModal').click(function () {
    $('.smsModal').hide();
    $('.smsImportBtn').removeAttr('disabled', 'readonly').html('Import').css({
        'cursor': 'pointer',
        'color': '#fff',
        'background': '#4CAF50'
    });

    $('#smsFile').css('border', '1px solid #ced4da');
});