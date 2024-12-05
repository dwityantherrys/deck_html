var pusher = new Pusher('f755c448daaff8377a46', { cluster: 'ap1' });
var pusherChannel = pusher.subscribe('new-chat-customer');
var soundNewChatCustomer = document.querySelector("#salesChatAudio");

// soundNewChatCustomer.play()

pusherChannel.bind('new-chat-customer', function(data) {
    soundNewChatCustomer.play();
    console.log('new chat from customer');
    appChat.newChatCustomer = true;
});

var select2AjaxHandler = function(selector, url, selectedId, onlyLoadSelected = false) {
    if(!onlyLoadSelected) {
        $(selector).select2({
            ajax: {
                url: `${url}`,
                data: function(params) {
                    var query = { searchKey: params.term }
                    return query;
                },
                processResults: function(data) {
                    return {
                        results: data.results.map((item) => {
                            item.text = item.name
                            return item
                        })
                    }
                }
            },
        })
    }

    if (selectedId) {
        var uri = selectedId !== 'custom' ? `${url}/${selectedId}` : url;

        $.ajax({
            type: "GET",
            url: uri,
            success: function(response) {
                var newOption = new Option(`${response.name}`, response.id, true, true);
                $(selector).append(newOption).trigger('change');
            },
            error: function(err) { console.log(`failed fetch : ${err}`) }
        });
    }
}

$(document).ready(function() {
    /* toggle field handler */
    if ($(".has-constraint").val() == 1) {
        var target = $(".has-constraint").data("target");
        $(target).show();
    }
    $(".has-constraint").change(function() {
        var target = $(this).data("target");
        var value = $(this).val();

        value == 1 ? $(target).show() : $(target).hide();
    });
    /* end toggle field handler */

    /* image preview handler */
    $(".has-image-preview").change(function(e) {
        var self = $(this)
        var oFReader = new FileReader();
        var target = self.data("target") ? self.data("target") : ".image-preview";

        oFReader.readAsDataURL(e.target.files[0]);
        oFReader.onload = function(oFREvent) {
            $(target).attr("src", oFREvent.target.result);

            self.prev('.show-image-preview').data('url', oFREvent.target.result)
            self.prev('.show-image-preview').show()
        };
    });
    $(".show-image-preview").click(function (e) {
        $('.image-preview').attr("src", $(this).data('url'));
    })
    /* end image preview handler */

    /* corfimation handler */
    $(".confirmation-print").click(function() {

        var target = $(this).data("target");
        var printInformation = $(this).data("information") ? $(this).data("information") : '';

        $.confirm({
            title: "Confirmation Print!",
            content: `Tekan tombol Confirm untuk mencetak dokumen ini, ${printInformation}`,
            buttons: {
                confirm: {
                    btnClass: "btn-dark",
                    action: function() {
                        window.location = target

                        $.alert({
                            title: "Print Success",
                            content: "tunggu sampai indicator loading pada browser hilang. lalu click ok",
                            buttons: {
                                ok: function() {
                                    window.stop();
                                    location.reload();
                                }
                            }
                        });
                    }
                },
                cancel: function() {}
            }
        });
    });

    $(".confirmation-update").click(function() {
        var formId = $(this).data("formid");
        var formData = $(`.file-uploaded__form-${formId} .form`).children('input');
        var target = $(this).data("target");
        var data = {};

        $.each(formData, function(i, field) {
            if (field.type == 'checkbox') {
                data[field.name] = field.checked
            } else {
                data[field.name] = field.value
            }

        });

        $.confirm({
            title: "Confirmation Update!",
            content: "",
            buttons: {
                confirm: {
                    btnClass: "btn-success",
                    action: function() {
                        $.ajax({
                            type: "PUT",
                            url: target,
                            data: data,
                            success: function() {
                                $.alert({
                                    title: "Update Success",
                                    content: "Data berhasil diperbarui.",
                                    buttons: {
                                        ok: function() { location.reload(); }
                                    }
                                });
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                $.alert("Gagal memperbarui data data.");
                                console.log(xhr.status, thrownError);
                            }
                        });
                    }
                },
                cancel: function() {}
            }
        });
    });

    $(".confirmation-paid").click(function() {
        var _token = $(this).data("token");
        var target = $(this).data("target");

        $.confirm({
            title: "Confirmation Update Transaction Paid!",
            content: "Data yang telah diperbarui tidak dapat di kembalikan lagi.",
            buttons: {
                confirm: {
                    btnClass: "btn-success",
                    action: function() {
                        $.ajax({
                            type: "PUT",
                            url: target,
                            data: { _token },
                            success: function() {
                                $.alert({
                                    title: "Update Success",
                                    content: "Data berhasil diperbarui.",
                                    buttons: {
                                        ok: function() { location.reload(); }
                                    }
                                });
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                $.alert("Gagal memperbarui data.");
                                console.log(xhr.status, thrownError);
                            }
                        });
                    }
                },
                cancel: function() {}
            }
        });
    });

    $(".confirmation-delivered").click(function() {
        var _token = $(this).data("token");
        var target = $(this).data("target");

        $.confirm({
            title: "Confirmation Update Transaction Delivered!",
            content: "Data yang telah diperbarui tidak dapat di kembalikan lagi.",
            buttons: {
                confirm: {
                    btnClass: "btn-success",
                    action: function() {
                        $.ajax({
                            type: "PUT",
                            url: target,
                            data: { _token },
                            success: function() {
                                $.alert({
                                    title: "Update Success",
                                    content: "Data berhasil diperbarui.",
                                    buttons: {
                                        ok: function() { location.reload(); }
                                    }
                                });
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                $.alert("Gagal memperbarui data.");
                                console.log(xhr.status, thrownError);
                            }
                        });
                    }
                },
                cancel: function() {}
            }
        });
    });

    $(".confirmation-delete").click(function() {
        var _token = $(this).data("token");
        var target = $(this).data("target");

        $.confirm({
            title: "Confirmation Delete!",
            content: "Data yang telah dihapus tidak dapat di kembalikan lagi.",
            buttons: {
                confirm: {
                    btnClass: "btn-danger",
                    action: function() {
                        $.ajax({
                            type: "DELETE",
                            url: target,
                            data: { _token },
                            success: function() {
                                $.alert({
                                    title: "Delete Success",
                                    content: "Data berhasil dihapus.",
                                    buttons: {
                                        ok: function() { location.reload(); }
                                    }
                                });
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                $.alert("Gagal menghapus data.");
                                console.log(xhr.status, thrownError);
                            }
                        });
                    }
                },
                cancel: function() {}
            }
        });
    });
    /* end corfimation handler */

    /**prompt handler */
    $(".prompt-delete").click(function() {
        var _token = $(this).data("token");
        var target = $(this).data("target");

        $.confirm({
            title: "Confirmation Delete/Cancel Transaction!",
            content: "Data yang telah dihapus tidak dapat di kembalikan lagi. " +
            '<form action="" class="formName">' +
            '<div class="form-group">' +
            '<label>Catatan Delete/Cancel</label>' +
            `<select class="form-control canceledReason" required>
            <option value="" selected disabled hidden>Pilih Alasan Penolakan</option>
            <option value="Lost Order Kalah Harga">Lost Order Kalah Harga</option>
            <option value="Stok Kosong">Stok Kosong</option>
            <option value="Salah Input">Salah Input</option>
            <option value="Lain Lain">Lain Lain</option>
            </select>` +
            '</div>' +
            '</form>',
            buttons: {
                formSubmit: {
                    text: 'confirm',
                    btnClass: "btn-danger",
                    action: function () {
                        var canceledReason = this.$content.find('.canceledReason').val();
                        if(!canceledReason){
                            $.alert('provide a valid input');
                            return false;
                        }

                        $.ajax({
                            type: "DELETE",
                            url: target,
                            data: {
                                _token: _token,
                                canceled_reason: canceledReason
                            },
                            success: function() {
                                $.alert({
                                    title: "Delete Success",
                                    content: "Data berhasil dihapus.",
                                    buttons: {
                                        ok: function() { location.reload(); }
                                    }
                                });
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                if(xhr.responseJSON.message) {
                                    $.alert("Gagal menghapus data, " + xhr.responseJSON.message);
                                    return
                                }

                                $.alert("Gagal menghapus data.");
                                console.log(xhr, thrownError);
                            }
                        });
                    }
                },
                cancel: function () { },
            },
            onContentReady: function () {
                // bind to events
                var jc = this;
                this.$content.find('form').on('submit', function (e) {
                    // if the user submits the form by pressing enter in the field.
                    e.preventDefault();
                    jc.$$formSubmit.trigger('click'); // reference the button and click it
                });
            }
        });
    });

    $(".prompt-paid").click(function() {
        var _token = $(this).data("token");
        var target = $(this).data("target");

        $.confirm({
            title: "Confirmation Update Transaction Paid!",
            content: "Data yang telah diperbarui tidak dapat di kembalikan lagi. " +
            '<form action="" class="formName">' +
            '<div class="form-group">' +
            '<label>Purchase Invoice Number</label>' +
            '<input type="text" placeholder="Purchase Invoice Number" class="purchaseInvoiceNumber form-control" required />' +
            '</div>' +
            '</form>',
            buttons: {
                formSubmit: {
                    text: 'confirm',
                    btnClass: "btn-success",
                    action: function () {
                        var purchaseInvoiceNumber = this.$content.find('.purchaseInvoiceNumber').val();
                        if(!purchaseInvoiceNumber){
                            $.alert('provide a valid invoice number');
                            return false;
                        }

                        $.ajax({
                            type: "PUT",
                            url: target,
                            data: {
                                _token: _token,
                                purchase_invoice_number: purchaseInvoiceNumber
                            },
                            success: function() {
                                $.alert({
                                    title: "Update Success",
                                    content: "Data berhasil diperbarui.",
                                    buttons: {
                                        ok: function() { location.reload(); }
                                    }
                                });
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                if(xhr.responseJSON.message) {
                                    $.alert("Gagal memperbarui data, " + xhr.responseJSON.message);
                                    return
                                }

                                $.alert("Gagal memperbarui data.");
                                console.log(xhr, thrownError);
                            }
                        });
                    }
                },
                cancel: function () { },
            },
            onContentReady: function () {
                // bind to events
                var jc = this;
                this.$content.find('form').on('submit', function (e) {
                    // if the user submits the form by pressing enter in the field.
                    e.preventDefault();
                    jc.$$formSubmit.trigger('click'); // reference the button and click it
                });
            }
        });
    });
    /**end prompt handler */
});
