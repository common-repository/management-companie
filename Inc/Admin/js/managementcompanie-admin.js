(function ($) {
    const {__, _x, _n, _nx} = wp.i18n;
    'use strict';
    let mindate = $('.mc-datepicker').first().val();
    $(".mc-datepicker").datepicker({
        dateFormat: "dd-mm-yy",
        minDate: new Date($.datepicker.formatDate("yy-dd-mm", new Date(mindate))),
        maxDate: new Date()
    });

    $('#invoices').on("click", 'button.charge-bank', function (e) {
        e.preventDefault();
        let invoiceid = $(this).data('invoiceid');
        let order = $(this).data('order');
        let suma = Number($(this).closest('.charge-bank-line').find('.bank-amount').val());
        let date = $(this).closest('.charge-bank-line').find('.mc-datepicker').val();

        $.confirm({
            title: __('Charge amount via bank', 'management-companie'),
            content: __('Are you sure you want to charge the selected amount via bank for this invoice?', 'management-companie'),
            buttons: {
                da: {
                    text: __('YES', 'management-companie'),
                    action: function () {
                        $.ajax({
                            type: "POST",
                            url: mc.ajaxurl,
                            data: {
                                'action': 'incaseaza_banca_factura',
                                'invoiceid': invoiceid,
                                'suma': suma,
                                'order': order,
                                'date': date,
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                set_page_loading(__('Please wait while we charge the selected amount', 'management-companie'));
                            },
                            success: function (result) {
                                if (result.response.error_code === null) {
                                    $.confirm({
                                        title: __('Success', 'management-companie'),
                                        content: __('The amount successfuly charged via bank. The page will be reloaded after you click OK button', 'management-companie'),
                                        buttons: {
                                            ok: {
                                                text: "OK",
                                                action: function () {
                                                    location.reload();
                                                }
                                            }
                                        },
                                        type: 'green',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                } else {
                                    let message = '';
                                    let error_reason = $.parseJSON(result.response.error_reason);
                                    message += error_reason.reason;
                                    if (error_reason.errors.length) {
                                        $.each($.parseJSON(error_reason.errors), function (index, value) {
                                            message += "\n" + value;
                                        });
                                    }
                                    $.alert({
                                        title: __('There was an error charging the selected amount', 'management-companie'),
                                        content: message,
                                        type: 'red',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                }
                            },
                            error: function () {
                                $.alert({
                                    title: __('There was an error charging the selected amount', 'management-companie'),
                                    content: __('The server did not respond as expected', 'management-companie'),
                                    type: 'red',
                                    typeAnimated: true,
                                    boxWidth: '30%',
                                    useBootstrap: false,
                                });
                            },
                            complete: function () {
                                unset_page_loading();
                            }
                        });
                    },
                },
                nu: {
                    text: __('NO', 'management-companie'),
                    action: function () {

                    }
                },
            },
            type: 'blue',
            typeAnimated: true,
            boxWidth: '30%',
            useBootstrap: false,
        });
    });

    $('#invoices').on("click", 'button.generate-receipt', function (e) {
        e.preventDefault();
        let invoiceid = $(this).data('invoiceid');
        let order = $(this).data('order');
        let suma = Number($(this).closest('.generate-receipt-line').find('.receipt-amount').val());

        $.confirm({
            title: __('Generate receipt', 'management-companie'),
            content: __('Are you sure you want to generate a receipt for this invoice?', 'management-companie'),
            buttons: {
                da: {
                    text: __('YES', 'management-companie'),
                    action: function () {
                        $.ajax({
                            type: "POST",
                            url: mc.ajaxurl,
                            data: {
                                'action': 'genereaza_chitanta_factura',
                                'invoiceid': invoiceid,
                                'suma': suma,
                                'order': order
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                set_page_loading(__('Please wait while generation the receipt', 'management-companie'));
                            },
                            success: function (result) {
                                if (result.response.error_code === null) {
                                    $.confirm({
                                        title: __('Success', 'management-companie'),
                                        content: __('The receipt was successfully generated. The page will be reloaded after you click OK button', 'management-companie'),
                                        buttons: {
                                            ok: {
                                                text: "OK",
                                                action: function () {
                                                    location.reload();
                                                }
                                            }
                                        },
                                        type: 'green',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                } else {
                                    let message = '';
                                    let error_reason = $.parseJSON(result.response.error_reason);
                                    message += error_reason.reason;
                                    if (error_reason.errors.length) {
                                        $.each($.parseJSON(error_reason.errors), function (index, value) {
                                            message += "\n" + value;
                                        });
                                    }
                                    $.alert({
                                        title: __('There was an error generating the receipt', 'management-companie'),
                                        content: message,
                                        type: 'red',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                }
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                $.alert({
                                    title: __('There was an error generating the receipt', 'management-companie'),
                                    content: __('The server did not respond as expected', 'management-companie'),
                                    type: 'red',
                                    typeAnimated: true,
                                    boxWidth: '30%',
                                    useBootstrap: false,
                                });
                            },
                            complete: function () {
                                unset_page_loading();
                            }
                        });
                    },
                },
                nu: {
                    text: __('NO', 'management-companie'),
                    action: function () {

                    }
                },
            },
            type: 'blue',
            typeAnimated: true,
            boxWidth: '30%',
            useBootstrap: false,
        });
    });

    $('#invoices').on("click", 'button.cancel-invoice', function (e) {
        e.preventDefault();
        let invoiceid = $(this).data('invoiceid');
        let order = $(this).data('order');

        $.confirm({
            title: __('Cancel invoice', 'management-companie'),
            content: __('Are you sure you want to cancel the invoice for this order?', 'management-companie'),
            buttons: {
                da: {
                    text: __('YES', 'management-companie'),
                    action: function () {
                        $.ajax({
                            type: "POST",
                            url: mc.ajaxurl,
                            data: {
                                'action': 'anuleaza_factura',
                                'invoiceid': invoiceid,
                                'order': order
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                set_page_loading(__('Please wait while cancelling the invoice', 'management-companie'));
                            },
                            success: function (result) {
                                if (result.response.error_code === null) {
                                    $.confirm({
                                        title: __('Success', 'management-companie'),
                                        content: __('The invoice was successfully cancelled. The page will be reloaded after you click OK button', 'management-companie'),
                                        buttons: {
                                            ok: {
                                                text: "OK",
                                                action: function () {
                                                    location.reload();
                                                }
                                            }
                                        },
                                        type: 'green',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                } else {
                                    let message = '';
                                    let error_reason = $.parseJSON(result.response.error_reason);
                                    message += error_reason.reason;

                                    if (error_reason.errors.length) {
                                        $.each($.parseJSON(error_reason.errors), function (index, value) {
                                            message += "\n" + value;
                                        });
                                    }
                                    $.alert({
                                        title: __('There was an error cancelling the invoice', 'management-companie'),
                                        content: message,
                                        type: 'red',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                }
                            },
                            error: function () {
                                $.alert({
                                    title: __('There was an error cancelling the invoice', 'management-companie'),
                                    content: __('The server did not respond as expected', 'management-companie'),
                                    type: 'red',
                                    typeAnimated: true,
                                    boxWidth: '30%',
                                    useBootstrap: false,
                                });
                            },
                            complete: function () {
                                unset_page_loading();
                            }
                        });
                    },
                },
                nu: {
                    text: __('NO', 'management-companie'),
                    action: function () {

                    }
                },
            },
            type: 'blue',
            typeAnimated: true,
            boxWidth: '30%',
            useBootstrap: false,
        });
    });

    $('#invoices').on("click", 'button.view-invoice', function (e) {
        e.preventDefault();
        let invoiceid = $(this).data('invoiceid');
        let order = $(this).data('order');

        $.alert({
            title: false,
            content: function () {
                let self = this;
                return $.ajax({
                    type: 'POST',
                    url: mc.ajaxurl,
                    data: {
                        'action': 'vizualizeaza_factura',
                        'invoiceid': invoiceid,
                        'order': order
                    },
                    dataType: 'html'
                }).done(function (response) {
                    self.setContentAppend(response);
                }).fail(function () {
                    $.alert({
                        title: __('There was an error viewing the invoice', 'management-companie'),
                        content: __('The server did not respond as expected', 'management-companie'),
                        type: 'red',
                        typeAnimated: true,
                        boxWidth: '30%',
                        useBootstrap: false,
                    });
                });
            }
        });
    });

    $('#invoices').on("click", 'button.view-receipt', function (e) {
        e.preventDefault();
        let receiptid = $(this).data('receiptid');
        let order = $(this).data('order');

        $.alert({
            title: false,
            content: function () {
                let self = this;
                return $.ajax({
                    type: 'POST',
                    url: mc.ajaxurl,
                    data: {
                        'action': 'vizualizeaza_chitanta',
                        'receiptid': receiptid,
                        'order': order
                    },
                    dataType: 'html'
                }).done(function (response) {
                    self.setContentAppend(response);
                }).fail(function () {
                    $.alert({
                        title: __('There was an error viewing the invoice', 'management-companie'),
                        content: __('The server did not respond as expected', 'management-companie'),
                        type: 'red',
                        typeAnimated: true,
                        boxWidth: '30%',
                        useBootstrap: false,
                    });
                });
            }
        });
    });

    $('#generate-invoice').on("click", function (e) {
        e.preventDefault();

        let order = $(this).data('order');

        $.confirm({
            title: __('Generate invoice', 'management-companie'),
            content: __('Are you sure you want to generate an invoice for this order?', 'management-companie'),
            buttons: {
                da: {
                    text: __('YES', 'management-companie'),
                    action: function () {
                        $.ajax({
                            type: "POST",
                            url: mc.ajaxurl,
                            data: {
                                'action': 'adauga_factura',
                                'order': order
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                set_page_loading(__('Please wait while generating invoice', 'management-companie'));
                            },
                            success: function (result) {
                                if (result.response.error_code === null) {
                                    $.confirm({
                                        title: __('Success', 'management-companie'),
                                        content: __('The invoice was successfully generated. The page will be reloaded after you click OK button', 'management-companie'),
                                        buttons: {
                                            ok: {
                                                text: "OK",
                                                action: function () {
                                                    location.reload();
                                                }
                                            }
                                        },
                                        type: 'green',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                } else {
                                    let message = '';
                                    let error_reason = $.parseJSON(result.response.error_reason);
                                    message += error_reason.reason;
                                    if (error_reason.errors.length) {
                                        $.each($.parseJSON(error_reason.errors), function (index, value) {
                                            message += "\n" + value;
                                        });
                                    }
                                    $.alert({
                                        title: __('There was an error generating the invoice', 'management-companie'),
                                        content: message,
                                        type: 'red',
                                        typeAnimated: true,
                                        boxWidth: '30%',
                                        useBootstrap: false,
                                    });
                                }
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                $.alert({
                                    title: __('There was an error generating the invoice', 'management-companie'),
                                    content: __('The server did not respond as expected', 'management-companie'),
                                    type: 'red',
                                    typeAnimated: true,
                                    boxWidth: '30%',
                                    useBootstrap: false,
                                });
                            },
                            complete: function () {
                                unset_page_loading();
                            }
                        });
                    },
                },
                nu: {
                    text: __('NO', 'management-companie'),
                    action: function () {

                    }
                },
            },
            type: 'blue',
            typeAnimated: true,
            boxWidth: '30%',
            useBootstrap: false,
        });
    });


    function set_page_loading(e) {
        if (e) $("#pl-msg").text(e); else $("#pl-msg").text("Please wait, processing");
        $("#page-loading").removeClass().addClass("visible")
    }

    function unset_page_loading() {
        $("#page-loading").removeClass().addClass("hidden")
    }

})
(jQuery);

