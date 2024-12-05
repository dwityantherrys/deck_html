$('[data-toggle="tooltip"]').tooltip();

$(".has-ajax-form").click(function() {
    var url = $(this).data('load') + '/invoice-format';
    var formUrl = $(this).data('form-url');
    var isSuperAdmin = $(this).data('is-superadmin') == 1 ? true : false;

    // initial action
    $("#form-ajax-form input[value='save']").attr("type", "submit");
    $("#form-ajax-form button[value='save_print']").attr("type", "submit");
    $("#form-ajax-form input[value='save']").attr('disabled', false);
    $("#form-ajax-form button[value='save_print']").attr('disabled', false);
    $("#form-ajax-form .confirmation-print").attr('disabled', false);

    $.ajax({
        type: "GET",
        url: url,
        success: function(response) {
            // set value form
            $("#form-ajax-form").attr("action", `${formUrl}/${response.id}`);
            $('input[name="id"]').val(response.id);
            $('input[name="date"]').val(response.date_formated);
            $('input[name="number"]').val(response.number);
            $('input[name="downpayment"]').val(response.downpayment);

            select2AjaxHandler('select[name="purchase_receive_number"]', `${baseBeApiUrl}/purchase/receipt`, response.purchase_receive_id, true);
            select2AjaxHandler('select[name="vendor_id"]', `${baseBeApiUrl}/customer`, response.purchase_receive.purchase.vendor_id);
            
            downpaymentField.set(response.downpayment);
            
            app.elements = response.invoice_details

            // set params confirmation print
            var printInformation = response.log_print ? 'di print oleh ' + response.log_print.employee.name + ' pada tanggal ' + response.log_print.date_formated : ''
            $("#form-ajax-form .confirmation-print").attr("data-original-title", printInformation);
            $("#form-ajax-form .confirmation-print").data("target", `${formUrl}/${response.id}/print`);
            $("#form-ajax-form .confirmation-print").data("information", printInformation);

            // disable receive number
            $('select[name="request_number"]').attr('disabled', true);

            // disable button if has printed
            var isDisable = (response.log_print && !isSuperAdmin) ? true : false;
            if(isDisable || response.deleted_at) {
                $("#form-ajax-form input[value='save']").attr('disabled', true);
                $("#form-ajax-form button[value='save_print']").attr('disabled', true);
                $("#form-ajax-form input[value='save']").attr("type", "button");
                $("#form-ajax-form button[value='save_print']").attr("type", "button");

                if(response.deleted_at) $("#form-ajax-form .confirmation-print").attr('disabled', true);
            }

            if(isDisable) $("#form-ajax-form").attr("action", ``);
        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});