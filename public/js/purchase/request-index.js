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
                // set value form
                $("#form-ajax-form").attr("action", `${formUrl}/${response.id}`);
                $('input[name="id"]').val(response.id);
                $('input[name="request_date"]').val(response.request_date_formated);
                $('input[name="request_number"]').val(response.request_number);
                $('input[name="pat_number"]').val(response.pat_number);
    
                select2AjaxHandler('select[name="sales_id"]', `${baseBeApiUrl}/sales/order`, response.sales_id);
                select2AjaxHandler('select[name="request_by"]', `${baseBeApiUrl}/employee`, response.request_by);
                select2AjaxHandler('select[name="vendor_id"]', `${baseBeApiUrl}/customer`, response.vendor_id);
                select2AjaxHandler('select[name="branch_id"]', `${baseBeApiUrl}/branch`, response.branch_id);
                
                app.elements = response.purchase_details
    
                $('select[name="request_type"]').val(response.request_type);
                $('select[name="request_type"]').trigger('change');

                $('select[name="destination"]').val(response.destination);
                $('select[name="destination"]').trigger('change');
    
                // set params confirmation print
                var printInformation = response.request_log_print ? 'di print oleh ' + response.request_log_print.employee.name + ' pada tanggal ' + response.request_log_print.date_formated : ''
                $("#form-ajax-form .confirmation-print").attr("data-original-title", printInformation);
                $("#form-ajax-form .confirmation-print").data("target", `${formUrl}/${response.id}/print`);
                $("#form-ajax-form .confirmation-print").data("information", printInformation);
    
                // disable button if has printed
                var isDisable = (response.request_log_print && (!isSuperAdmin || response.order_number)) ? true : false;
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
})