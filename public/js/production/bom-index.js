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

            select2AjaxHandler('select[name="item_id"]', `${baseBeApiUrl}/item-material`, response.item_id);
            $('input[name="manufacture_quantity"]').val(response.manufacture_quantity);

            app.elements = response.bom_details
          
            // disable button if has printed
            // var isDisable = (response.quotation_log_print && (!isSuperAdmin || response.order_number)) ? true : false;
            // if(isDisable || response.deleted_at) {
            //     $("#form-ajax-form input[value='save']").attr('disabled', true);
            //     $("#form-ajax-form button[value='save_print']").attr('disabled', true);
            //     $("#form-ajax-form input[value='save']").attr("type", "button");
            //     $("#form-ajax-form button[value='save_print']").attr("type", "button");

            //     if(response.deleted_at) $("#form-ajax-form .confirmation-print").attr('disabled', true);
            // }

            if(isDisable) $("#form-ajax-form").attr("action", ``);
        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});