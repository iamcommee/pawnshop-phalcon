$(function () {

    $('.transaction_date').datepicker({
        format: "DD/MM/YYYY",
        language: "th-TH",
        autoHide: "true",
        autoPick: "true",
        zIndex: "2000"
    });

    moment.locale('th');

    var separate_sale_table = $('#separate_sale_table').DataTable({
        ajax: {
            url: "server_processing",
            dataSrc: ""
        },
        columns: [{
                data: {
                    product_id: "product_id",
                    agreement_nubmer: "agreement_number",
                    account_id: "account_id",
                    link: "link"
                },
                render: function (data) {
                    return data['link'];
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
                data: "product_value"
            },
            {
                data: "sale_value"
            },
            {
                data: {
                    tag: "tag"
                },
                render: function (data, row) {
                    return '<span>' + data['tag'] + '</span>';
                }
            },
            {
                data: "transaction_date"
            },
            {
                data: "note"
            },
            {
                data: {
                    agreement_nubmer: "agreement_nubmer",
                },
                render: function (data) {
                    return '<span class="separate-sale color-black button-click" value="' + data.agreement_nubmer + '"> แยกขาย </span>';
                }
            },
            {
                data: {
                    tag: "tag",
                },
                render: function (data) {
                    if (data['tag'] == 'สินค้าหลัก') {
                        return '';
                    } else {
                        return '<span class="edit-separate-sale color-black button-click"> แก้ไข </span>';
                    }
                }
            }
        ],
        rowCallback: function (data, row) {
            if (data.tag == 'สินค้าหลัก') {

            } else {
                $(row).addClass('separate_product');
            }
        },
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
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [{
            extend: 'excel',
            text: 'ดาวน์โหลดตาราง',
            className: 'btn btn-custom btn-outline-primary x-rounded',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                format: {
                    header: function (data, columnIdx) {
                        if (columnIdx == 1) {
                            return 'สินค้า';
                        } else if (columnIdx == 2) {
                            return 'ยี่ห้อ';
                        } else if (columnIdx == 3) {
                            return 'รายละเอียด';
                        } else {
                            return data;
                        }
                    }
                }
            }
        }],
        rowCallback: function (row, data) {
            if (data.tag == 'สินค้าหลัก') {
                // ---
            } else {
                $(row).addClass("separate_product");
            }
        },
        footerCallback: function () {
            var api = this.api();

            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i : 0;
            };

            total = api
                .column(4, {
                    search: 'applied'
                })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            $(api.column(4).footer()).html(
                $.number(total)
            );

            total = api
                .column(5, {
                    search: 'applied'
                })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            $(api.column(5).footer()).html(
                $.number(total)
            );
        },
        initComplete: function () {
            $('.separate_sale_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            separate_sale_table.columns.adjust();
        }
    });

    yadcf.init(separate_sale_table, [{
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

    $("#separate_sale_table tbody").on('click', '.separate-sale', function () {
        var data_string = separate_sale_table.rows($(this).parent('td').parent()).data();
        var product_id = data_string[0]['product_id'];
        var product_name = data_string[0]['product_name'];
        var agreement_nubmer = data_string[0]['agreement_number'];
        $('.modal-title').text('แยกขาย' + ' ' + product_name + ' เลขที่สัญญา ' + agreement_nubmer);
        $('.product_id').val(product_id);
        $('#separateSaleModal').modal('show');
        $('#separateSaleModal').on('shown.bs.modal', function () {
            $('.transaction_time').val(moment().format('HH:mm'));
            $('.product_name').val('');
            $('.product_brand').val('');
            $('.product_detail').val('');
            $('.product_value').val('');
            $('.sale_value').val('');
            $('.note').val('');
        });
    });

    $('.confirm-separate-sale').attr('disabled', true);
    $('.sale_value').keyup(function () {
        if ($(this).val().length != 0)
            $('.confirm-separate-sale').attr('disabled', false);
        else
            $('.confirm-separate-sale').attr('disabled', true);
    });

    $('.separateSaleForm').on('submit', function (e) {
        e.preventDefault();
        var transaction_date = $('.transaction_date').val();
        var transaction_time = $('.transaction_time').val();
        var product_id = $('.product_id').val();
        var product_name = $('.product_name').val();
        var product_brand = $('.product_brand').val();
        var product_detail = $('.product_detail').val();
        var product_value = $('.separateSaleForm .product_value').val();
        var sale_value = $('.separateSaleForm .sale_value').val();
        var note = $('.note').val();
        $.ajax({
            type: 'GET',
            url: 'separate-sale',
            data: {
                transaction_date: transaction_date,
                transaction_time: transaction_time,
                product_id: product_id,
                product_name: product_name,
                product_brand: product_brand,
                product_detail: product_detail,
                product_value: product_value,
                sale_value: sale_value,
                note: note
            },
            success: function () {
                separate_sale_table.ajax.reload(null, false);
            }
        });
        $('#separateSaleModal').modal('hide');
    });

    $('#separate_sale_table tbody').on('click', '.edit-separate-sale', function () {
        var data_string = separate_sale_table.rows($(this).parent('td').parent()).data();
        var account_id = data_string[0]['account_id'];
        var sale_value = data_string[0]['sale_value'];
        var note = data_string[0]['note'];
        $('.modal-title').text('แก้ไข' + ' ' + account_id);
        $('#editseparateSaleModal').modal('show');
        $('#editseparateSaleModal').on('shown.bs.modal', function () {
            $('.account_id').val(account_id);
            $('.sale_value').val(sale_value);
            $('.note').val(note);
            $('.confirm-delete-separate-sale-transaction').attr('data-account_id', account_id);
        });
    });

    $('.editseparateSaleForm').on('submit', function (e) {
        e.preventDefault();
        var account_id = $('.account_id').val();
        var sale_value = $('.editseparateSaleForm .sale_value').val();
        var note = $('.editseparateSaleForm .note').val();
        $.ajax({
            type: 'GET',
            url: 'edit-separate-sale-transaction',
            data: {
                account_id: account_id,
                sale_value: sale_value,
                note: note
            },
            success: function () {
                separate_sale_table.ajax.reload(null, false);
            }
        });
        $('#editseparateSaleModal').modal('hide');
    });

    $('.editseparateSaleForm').on('click', 'button.confirm-delete-separate-sale-transaction', function () {
        var account_id = $('.account_id').val();
        $.ajax({
            type: 'GET',
            url: 'delete-separate-sale-transaction',
            data: {
                account_id: account_id
            },
            success: function () {
                separate_sale_table.ajax.reload(null, false);
            }
        });
        $('#editseparateSaleModal').modal('hide');
    });

    $("#separate_sale_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});