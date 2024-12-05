var transactionChannels = [
    { "label": "website", "label-color": "green", "icon": "fa fa-desktop" },
    { "label": "mobile", "label-color": "blue", "icon": "fa fa-mobile" }
  ];

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
            $('input[name="quotation_date"]').val(response.quotation_date_formated);
            select2AjaxHandler('select[name="created_by"]', `${baseBeApiUrl}/employee`, response.created_by);
            $('input[name="quotation_number"]').val(response.quotation_number);
            select2AjaxHandler('select[name="customer_id"]', `${baseBeApiUrl}/customer`, response.customer_id);
            select2AjaxHandler('select[name="warehouse_id"]', `${baseBeApiUrl}/warehouse`, response.warehouse_id);

            $('select[name="payment_bank_channel_id"]').val(response.payment_bank_channel_id);
            $('select[name="payment_bank_channel_id"]').trigger('change');

            app.elements = response.sales_details
            app.warehouse_id = response.warehouse_id
            app.discount = response.discount

            discountField.set(response.discount);
            downpaymentField.set(response.downpayment);

            $(".transaction_channel").html(`<small class="label bg-${transactionChannels[response.transaction_channel]['label-color']}">
                <i class="${transactionChannels[response.transaction_channel]['icon']}" style="margin-right: 5px;"></i>
                ${transactionChannels[response.transaction_channel]['label']}
            </small>`)

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