$(function () {

    $.ajax({
        url: "server_processing"
    });

    $.ajax({
        url: "getDashboard",
        dataType: "json",
        success: function (data) {
            $('.sum_pawn_value').text(data.sum_pawn_value + ' บาท');
            $('.count_pawn_transaction').text('จำนวนทั้งหมด ' + data.count_pawn_transaction + ' รายการ');

            $('.sum_interest_value').text(data.sum_interest_value + ' บาท');
            $('.count_interest_transaction').text('จำนวนทั้งหมด ' + data.count_interest_transaction + ' รายการ');

            $('.sum_withdraw_value').text(data.sum_withdraw_value + ' บาท');
            $('.count_withdraw_transaction').text('จำนวนทั้งหมด ' + data.count_withdraw_transaction + ' รายการ');

            $('.sum_sale_value').text(data.sum_sale_value + ' บาท');
            $('.count_sale_transaction').text('จำนวนทั้งหมด ' + data.count_sale_transaction + ' รายการ');
        }
    });

    $('.transaction_date').datepicker({
        format: "DD/MM/YYYY",
        language: "th-TH",
        autoHide: "true",
        autoPick: "true",
        zIndex: "2000"
    });

    moment.locale('th');

    var waiting_table = $('#waiting_table').DataTable({
        ajax: {
            url: 'getWaitingList',
            dataSrc: ""
        },
        columns: [{
                data: {
                    agreement_number: "agreement_number",
                    link: "link"
                },
                render: function (data) {
                    return data["link"];
                }
            },
            {
                data: "product_name"
            },
            {
                data: "product_brand"
            },
            {
                data: "product_detail"
            },
            {
                data: "start_date"
            },
            {
                data: "end_date"
            },
            {
                data: "product_value"
            },
            {
                data: {
                    agreement_nubmer: "agreement_nubmer",
                },
                render: function (data) {
                    return '<span class="move-to-sale color-black button-click" value="' + data.agreement_nubmer + '"> ตั้งขาย </span>';
                }
            }
        ],
        ordering: false,
        stateSave: true,
        scrollY: 600,
        scroller: true,
        oLanguage: {
            sInfoEmpty: "",
            sSearch: "ค้นหา",
            sInfo: "จำนวนทั้งหมด _TOTAL_ รายการ ",
            sZeroRecords: "ไม่มีข้อมูล",
            sInfoFiltered: "( ค้นหาจากทั้งหมด _MAX_ รายการ )",
            sLengthMenu: "_MENU_",
            oPaginate: {
                sFirst: "หน้าแรก",
                sPrevious: "ก่อนหน้า",
                sNext: "ถัดไป",
                sLast: "หน้าสุดท้าย"
            },
        },
        dom: "<'row'<'col-sm-6 col-md-6'<'toolbar'>><'col-sm-6 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        footerCallback: function () {
            var api = this.api();

            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i : 0;
            };

            total = api
                .column(6, {
                    search: 'applied'
                })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            $(api.column(6).footer()).html(
                $.number(total)
            );
        },
        initComplete: function () {
            $('.waiting_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            waiting_table.columns.adjust();
        }

    });

    yadcf.init(waiting_table, [{
        column_number: 1,
        filter_type: 'text',
        text_data_delimiter: ' ', // TODO on progress
        filter_default_label: 'ค้นหาสินค้า',
        filter_reset_button_text: false
    }, {
        column_number: 2,
        filter_type: 'text',
        text_data_delimiter: ' ', // TODO on progress
        filter_default_label: 'ค้นหายี่ห้อ',
        filter_reset_button_text: false
    }, {
        column_number: 3,
        filter_type: 'text',
        text_data_delimiter: ' ', // TODO on progress
        filter_default_label: 'ค้นหารายละเอียด',
        filter_reset_button_text: false
    }, ]);

    $('#waiting_table tbody').on('click', '.move-to-sale', function () {
        var data_string = waiting_table.rows($(this).parent('td').parent()).data();
        var product_id = data_string[0]['product_id'];
        var product_name = data_string[0]['product_name'];
        var agreement_nubmer = data_string[0]['agreement_number'];
        $('.modal-title').text('ตั้งขาย' + ' ' + product_name + ' เลขที่สัญญา ' + agreement_nubmer);
        $('#moveToSaleModal').modal('show');
        $('#moveToSaleModal').on('shown.bs.modal', function () {
            $('.product_id').val(product_id);
            $('.transaction_time').val(moment().format('HH.mm'));
            $('.sale_value').trigger('focus');
            $('.sale_value').val('');
            $('.note').val();
        });
    });

    $('.confirm-move-to-sale').attr('disabled', true);
    $('.sale_value').keyup(function () {
        if ($(this).val().length != 0)
            $('.confirm-move-to-sale').attr('disabled', false);
        else
            $('.confirm-move-to-sale').attr('disabled', true);
    });

    $('.moveToSaleForm').on('submit', function (e) {
        e.preventDefault();
        var product_id = $('.product_id').val();
        var transaction_date = $('.transaction_date').val();
        var transaction_time = $('.transaction_time').val();
        var sale_value = $('.sale_value').val();
        var note = $('.note').val();
        $.ajax({
            type: 'GET',
            url: 'move-to-sell',
            data: {
                product_id: product_id,
                transaction_date: transaction_date,
                transaction_time: transaction_time,
                sale_value: sale_value,
                note: note
            },
            success: function () {
                waiting_table.ajax.reload(null, false);
            }
        });
        $('#moveToSaleModal').modal('hide');
    });

    $("div.toolbar").html('สินค้าหลุดวันนี้');
    $("#waiting_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');

});