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
            console.log('good issued id: ', response.id)
            $('input[name="id"]').val(response.id);
            $('input[name="date"]').val(response.date_formated);
            $('input[name="number"]').val(response.number);
            $('select[name="warehouse_id"]').val(response.warehouse_id);
            $('select[name="factory_id"]').val(response.factory_id);
            $('select[name="warehouse_id"]').trigger('change');

            select2AjaxHandler('select[name="good_issued_id"]', `${baseBeApiUrl}/production/good-issued`, response.good_issued_id);
            select2AjaxHandler('select[name="created_by"]', `${baseBeApiUrl}/customer`, response.created_by, true);
            select2AjaxHandler('select[name="warehouse_id"]', `${baseBeApiUrl}/warehouse`, response.warehouse_id, true);
            select2AjaxHandler('select[name="factory_id"]', `${baseBeApiUrl}/warehouse`, response.factory_id, true);

            //set list orders
            $.ajax({
                type: "GET",
                url: `${baseBeApiUrl}/production/job-order/${response.good_issued.job_order_id}`,
                success: function(response) {
                    app.orders = response.job_order_details
                    $(`a[href="#collapseListOrders"]`).removeClass('disabled')
                },
                error: function(err) { console.log(`failed fetch : ${err}`) }
            });

            app.elements = response.good_receipt_details
            // app.warehouse_id = response.warehouse_id

            // set params confirmation print
            var printInformation = response.log_print ? 'di print oleh ' + response.log_print.employee.name + ' pada tanggal ' + response.log_print.date_formated : ''
            $("#form-ajax-form .confirmation-print").attr("data-original-title", printInformation);
            $("#form-ajax-form .confirmation-print").data("target", `${formUrl}/${response.id}/print`);
            $("#form-ajax-form .confirmation-print").data("information", printInformation);
            
            // disable button if has printed
            var isDisable = (response.log_print && (!isSuperAdmin || response.number)) ? true : false;
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