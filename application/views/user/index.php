<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/index.css'); ?>">

<div class="p-5">

    <!-- modal -->
    <div class="smsModal modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form enctype="multipart/form-data" method="post" id="smsUploadFile">
                    <div class="modal-body">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name() ?>" value="<?php echo $this->security->get_csrf_hash() ?>" class="csrf">
                        <!-- <h6 class="font-weight-bolder text-danger">
                            *CSV file must have header of only "Phonenumber"
                        </h6> -->
                        <input type="file" name="smsFile" id="smsFile" class="form-control" accept=".csv">
                        <span class="e_upload err"></span>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button class="btn btn-secondary smsCloseImportModal" type="button">Close</button>
                        <button class="btn smsImportBtn" type="submit">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- form -->
    <form method="post" id="smsForm" class="smsForm">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name() ?>" value="<?php echo $this->security->get_csrf_hash() ?>" class="csrf">

        <div class="import">
            <button class="btn smsOpenImportModal" type="button" style="background:#294a63;color:#fff">Import</button>
        </div>
        <hr>

        <div class=" form-group">
            <label>Mobile</label>
            <input type="number" name="mobile" class="form-control mobile" id="mobile" placeholder="Mobile number">
            <select class="form-control mobileSelect" name="mobileSelect" id="mobileSelect" readonly con="false">
            </select>
            <span class="e_mobile err">Invalid mobile length</span>
        </div>

        <div class="form-group">
            <label>Body</label>
            <textarea class="form-control msgBody" rows="9" name="msgBody"></textarea>
        </div>

        <div class="">
            <button class="btn btn-block smsSendBtn">Send</button>
        </div>
    </form>

</div>



<script src="<?php echo base_url('assets/js/index.js'); ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {

        //load bodyFile on pageLoad
        var csrfName = $('.csrf').attr('name');
        var csrfHash = $('.csrf').val();

        $.ajax({
            url: "<?php echo base_url('get-body'); ?>",
            method: "post",
            data: {
                [csrfName]: csrfHash
            },
            dataType: "json",
            success: function(res) {
                console.log(res);

                $(".msgBody").load("<?php echo base_url('body.txt'); ?>");

                $('.csrf').val(res.token);
            },
            error: function(res) {
                alert("An error occured");
                window.location.reload();
            }
        });


        //upload
        $('#smsUploadFile').on('submit', function(e) {
            e.preventDefault();

            var smsFile = $('#smsFile').val();

            if (smsFile == "" || smsFile == null) {
                $('#smsFile').css('border', '1px solid red');
                return false;
            } else {
                $('#smsFile').css('border', '1px solid #ced4da');
            }

            $.ajax({
                url: "<?php echo base_url('import-sms'); ?>",
                method: "post",
                data: new FormData(this),
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function(res) {
                    $('.e_upload').hide();

                    $('.smsImportBtn').attr('disabled', 'readonly').html('Importing...').css({
                        'cursor': 'not-allowed',
                        'background': '#F44336'
                    });
                },
                success: function(res) {
                    if (res.status === false) {
                        $('.e_upload').show().html(res.msg);

                        $('.csrf').val(res.token);
                    } else {
                        if (parseInt(res.length) > 1) {
                            for (i = 1; i < res.length; i++) {
                                $('#mobileSelect').append('<option disabled class="mobileSelectOptions">+91' + res[i].Phonenumber + '</option>').attr('con', 'true');
                            }

                            $('.mobile').hide();
                            $('.mobileSelect').show().css('border', '1px solid #ced4da');
                            $('.e_mobile').hide();

                            $('.smsModal').hide();
                        } else {
                            $('.e_upload').show().html("Empty file");
                        }

                        $('.csrf').val(res[0]);//token pos in resDataArray
                    }

                    $('.smsImportBtn').removeAttr('disabled', 'readonly').html('Import').css({
                        'cursor': 'pointer',
                        'color': '#fff',
                        'background': '#4CAF50'
                    });//reset importBtn
                },
                error: function(res) {
                    alert('Error importing data');
                    // window.location.reload();
                }
            });
        });


        //send
        $('.smsSendBtn').click(function(e) {
            e.preventDefault();

            var csrfName = $('.csrf').attr('name');
            var csrfHash = $('.csrf').val();
            var msgBody = $('.msgBody').val();
            var is_select = $('#mobileSelect').attr('con');

            if (is_select === "true") {
                var mobile = [];
                $(".mobileSelectOptions").each(function() {
                    var eachopt = $(this).val();
                    if (eachopt !== undefined && eachopt !== "" && eachopt !== null) {
                        mobile.push(eachopt);
                    }
                });

                if (parseInt(mobile.length) === 0) {
                    $('.mobileSelect').css('border', '1px solid red');
                    $('.e_mobile').html("No file uploaded").show();
                    $('#mobileSelect').attr('con', 'false');
                    return false;
                }

            } else if (is_select === "false") {
                var mobile = $('.mobile').val();

                if (mobile == "" || mobile == null) {
                    $('.mobile').css('border', '1px solid red');
                    return false;
                }
                if (mobile.length < 10 || mobile.length > 10) {
                    $('.mobile').css('border', '1px solid red');
                    $('.e_mobile').html("Invalid mobile length").show();
                    return false;
                } else {
                    $('.mobile').css('border', '1px solid #ced4da');
                    $('.e_mobile').hide();
                }
            }

            if (msgBody == "" || msgBody == null || msgBody == undefined) {
                $('.msgBody').css('border', '1px solid red');
                return false;
            } else {
                $('.msgBody').css('border', '1px solid #ced4da');
            }

            // console.log(mobile);
            // console.log(msgBody);

            $.ajax({
                url: "<?php echo base_url('send-sms'); ?>",
                method: "post",
                data: {
                    [csrfName]: csrfHash,
                    mobile: mobile,
                    msgBody: msgBody
                },
                dataType:"json",
                beforeSend: function(res) {
                    $('.smssendmultiplelinkbtn').attr('disabled', 'disabled');
                },
                success: function(res){
                    console.log(res);
                    
                    if (res.status === false) {
                        $('.e_mobile ').show().html(res.msg);
                    } else if (res.status === true){
                        // window.location.reload();
                    }

                    $('.csrf').val(res.token);
                },
                error: function(res) {
                    alert('Error importing data');
                    // window.location.reload();
                }
            })
        });

    });
</script>