$(document).ready(function() {
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
                console.log(response.job_order_details);
                // set value form
                $("#form-ajax-form").attr("action", `${formUrl}/${response.id}`);
                $('input[name="id"]').val(response.id);
                $('input[name="date"]').val(response.date_formated);
                $('input[name="number"]').val(response.number);
                $('input[name="location"]').val(response.location);

                $('select[name="type"]').val(response.type);
                $('select[name="type"]').trigger('change');

                select2AjaxHandler('select[name="created_by"]', `${baseBeApiUrl}/employee`, response.created_by);
                select2AjaxHandler('select[name="vendor_id"]', `${baseBeApiUrl}/customer`, response.vendor_id);

                app.elements = response.job_order_details
                app.vendor_id = response.vendor_id
            
                // set params confirmation print
                var printInformation = response.log_print ? 'di print oleh ' + response.log_print.employee.name + ' pada tanggal ' + response.log_print.date_formated : ''
                $("#form-ajax-form .confirmation-print").attr("data-original-title", printInformation);
                $("#form-ajax-form .confirmation-print").data("target", `${formUrl}/${response.id}/print`);
                $("#form-ajax-form .confirmation-print").data("information", printInformation);

                // disable request number
                $('select[name="quotation_number"]').attr('disabled', true);

                // disable button if has printed
                var isDisable = response.log_print && !isSuperAdmin ? true : false;
                if(isDisable) {
                    $("#form-ajax-form input[value='save']").attr('disabled', true);
                    $("#form-ajax-form button[value='save_print']").attr('disabled', true);
                    $("#form-ajax-form input[value='save']").attr("type", "button");
                    $("#form-ajax-form button[value='save_print']").attr("type", "button");
                }

                if(isDisable) $("#form-ajax-form").attr("action", ``);
            },
            error: function(err) { console.log(`failed fetch : ${err}`) }
        });
    });
})
