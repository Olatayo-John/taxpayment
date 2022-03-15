$('button.smsOpenImportModal').click(function () {
    var is_select = $('#mobileSelect').attr('con');
    if (is_select == "true") {
        var ans = confirm("Are you sure you want to import a new data? Your imported data will be cleared");
        if (ans == true) {
            $('.mobileSelectOptions').remove();
            $('.smsModal').show();
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
    $('.smsImportBtn').removeAttr('disabled','readonly').html('Import').css({
        'cursor':'pointer',
        'color':'#fff',
        'background':'#4CAF50'
    });

    $('#smsFile').css('border','1px solid #ced4da');
});