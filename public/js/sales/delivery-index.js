$('[data-toggle="tooltip"]').tooltip();

$(".has-ajax-form").click(function() {
    var url = $(this).data('load')
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
            var itemUri = ''
            // set value form
            $("#form-ajax-form").attr("action", `${formUrl}/${response.id}`);
            $('input[name="id"]').val(response.id);
            $('input[name="date"]').val(response.date_formated);
            $('input[name="number"]').val(response.number);
        

            select2AjaxHandler('select[name="job_order_id"]', `${baseBeApiUrl}/production/job-order`, response.job_order_id, true);
            select2AjaxHandler('select[name="vendor_id"]', `${baseBeApiUrl}/customer`, response.vendor_id, true);
            
            console.log(response.delivery_note_details);
            app.elements = response.delivery_note_details

            // set params confirmation print
            var printInformation = response.log_print ? 'di print oleh ' + response.log_print.employee.name + ' pada tanggal ' + response.log_print.date_formated : ''
            $("#form-ajax-form .confirmation-print").attr("data-original-title", printInformation);
            $("#form-ajax-form .confirmation-print").data("target", `${formUrl}/${response.id}/print`);
            $("#form-ajax-form .confirmation-print").data("information", printInformation);
            
            // disable button if has printed
            var isDisable = (response.quotation_log_print && (!isSuperAdmin || response.order_number)) ? true : false;
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