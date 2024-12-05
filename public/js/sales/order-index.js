var transactionChannels = [
    { "label": "website", "label-color": "green", "icon": "fa fa-desktop" },
    { "label": "mobile", "label-color": "blue", "icon": "fa fa-mobile" }
  ];

$('[data-toggle="tooltip"]').tooltip();

$(".has-ajax-form").click(function() {
    var itemUri = '';
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
            $('input[name="order_date"]').val(response.order_date_formated);
            $('input[name="order_number"]').val(response.order_number);
            $('input[name="discount"]').val(response.discount);
            $('input[name="downpayment"]').val(response.downpayment);

            $('select[name="shipping_method_id"]').val(response.shipping_method_id);
            $('select[name="shipping_method_id"]').trigger('change');

            $('select[name="payment_method_id"]').val(response.payment_method_id);
            $('select[name="payment_method_id"]').trigger('change');

            if(response.shipping_method_id == METHOD_PICKUP_POINT) {
                itemUri = 'warehouse';
            } else if(response.shipping_method_id == METHOD_DELIVERY) {
                itemUri = 'customer/' + response.customer_id + '/address';
            }

            select2AjaxHandler('select[name="shipping_address_id"]', `{{ $baseBeApiUrl }}/${itemUri}`, response.shipping_address_id);
            select2AjaxHandler('select[name="quotation_number"]', `${baseBeApiUrl}/sales/quotation`, response.id);
            select2AjaxHandler('select[name="created_by"]', `${baseBeApiUrl}/employee`, response.created_by);
            select2AjaxHandler('select[name="customer_id"]', `${baseBeApiUrl}/customer`, response.customer_id);
            select2AjaxHandler('select[name="warehouse_id"]', `${baseBeApiUrl}/warehouse`, response.warehouse_id);

            app.elements = response.sales_details
            app.warehouse_id = response.warehouse_id
            app.discount = response.discount

            $(".transaction_channel").html(`<small class="label bg-${transactionChannels[response.transaction_channel]['label-color']}">
                <i class="${transactionChannels[response.transaction_channel]['icon']}" style="margin-right: 5px;"></i>
                ${transactionChannels[response.transaction_channel]['label']}
            </small>`)

            // set params confirmation print
            var printInformation = response.order_log_print ? 'di print oleh ' + response.order_log_print.employee.name + ' pada tanggal ' + response.order_log_print.date_formated : ''
            $("#form-ajax-form .confirmation-print").attr("data-original-title", printInformation);
            $("#form-ajax-form .confirmation-print").data("target", `${formUrl}/${response.id}/print`);
            $("#form-ajax-form .confirmation-print").data("information", printInformation);

            // disable request number
            $('select[name="quotation_number"]').attr('disabled', true);

            // disable button if has printed
            var isDisable = response.order_log_print && !isSuperAdmin ? true : false;
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