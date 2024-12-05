
$('[data-toggle="tooltip"]').tooltip();

$(".has-ajax-form").click(function() {
    var id = $(this).data('id')
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
          if (response.loan_return != null) {
            $("#modal-footer").hide();
          }
          else {
            $("#modal-footer").show();
          }
            // set value form
            $("#form-ajax-form").attr("action", `${formUrl}/${response.id}`);
            $('input[name="id"]').val(response.id);
            $('input[name="loan_date"]').val(response.loan_date_formated);
            $('input[name="loan_expiration_date"]').val(response.loan_expiration_date_formated);
            $('input[name="loan_number"]').val(response.loan_number);
            select2AjaxHandler('select[name="created_by"]', `${baseBeApiUrl}/employee`, response.created_by);
            $('input[name="quotation_number"]').val(response.quotation_number);
            select2AjaxHandler('select[name="customer_id"]', `${baseBeApiUrl}/customer`, response.customer_id);
            select2AjaxHandler('select[name="warehouse_id"]', `${baseBeApiUrl}/warehouse`, response.warehouse_id);
            app.elements = response.loan_details
            app.warehouse_id = response.warehouse_id

            // set params confirmation print
            var printInformation = response.quotation_log_print ? 'di print oleh ' + response.quotation_log_print.employee.name + ' pada tanggal ' + response.quotation_log_print.date_formated : ''
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
