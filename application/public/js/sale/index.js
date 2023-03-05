$(function () {

    $('.transaction_date').datepicker({
        format: "DD/MM/YYYY",
        language: "th-TH",
        autoHide: "true",
        autoPick: "true",
        zIndex: "2000"
    });

    moment.locale('th');

    var sale_table = $('#sale_table').DataTable({
        ajax: {
            url: "server_processing",
            dataSrc: ""
        },
        columns: [{
                data: {
                    product_id: "product_id",
                    agreement_nubmer: "agreement_number",
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
                    agreement_nubmer: "agreement_nubmer",
                },
                render: function (data) {
                    if (data.tag == 'ตั้งขาย') {
                        return '<span class="edit-sale color-black button-click"> แก้ไข </span>';
                    } else {
                        return '<span class="edit-separate-sale color-black button-click"> แก้ไข </span>';
                    }
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
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [{
            text: 'ขายสินค้า',
            className: 'saleButton btn btn-custom btn-outline-primary x-rounded',
            action: function () {
                var sale_list_ = sale_table.rows('.selected').data();
                var sale_list_array = [];
                for (var i = 0; i < sale_list_.length; i++) {
                    var sale_list_string = sale_list_[i]['product_id'];
                    // เปลี่ยนเครื่องหมาย + ใน url
                    var product_id = sale_list_string.replace(new RegExp("\\+", "g"), "%2B");
                    sale_list_array.push(product_id);
                }
                var sale_list_json = JSON.stringify(sale_list_array);
                window.location.href = "create-sale-receipt/?sale_list_json=" + sale_list_json;
            }
        }, {
            text: 'มัดจำสินค้า',
            className: 'depositButton btn btn-custom btn-outline-primary x-rounded',
            action: function () {
                var deposit_list_ = sale_table.rows('.selected').data();
                var deposit_list_array = [];
                for (var i = 0; i < deposit_list_.length; i++) {
                    var deposit_list_string = deposit_list_[i]['product_id'];
                    // เปลี่ยนเครื่องหมาย + ใน url
                    var product_id = deposit_list_string.replace(new RegExp("\\+", "g"), "%2B");
                    deposit_list_array.push(product_id);
                }
                var deposit_list_json = JSON.stringify(deposit_list_array);
                window.location.href = "create-deposit-receipt/?deposit_list_json=" + deposit_list_json;
            }
        }, {
            extend: 'excel',
            text: 'ดาวน์โหลดตาราง',
            className: 'btn btn-custom btn-outline-primary x-rounded',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6, 7],
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
        }, ],
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
            $('.sale_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            sale_table.columns.adjust();
        }
    });

    yadcf.init(sale_table, [{
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

    $('#sale_table tbody').on('click', 'tr', function () {
        $(this).toggleClass('selected');
    });

    $("#sale_table tbody").on('click', '.separate-sale', function () {
        if ($(this).parent('td').parent().hasClass('selected')) {
            $(this).parent('td').parent().toggleClass('selected');
        }
        $(this).parent('td').parent().toggleClass('selected');
        var data_string = sale_table.rows($(this).parent('td').parent()).data();
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
        var product_value = $('.product_value').val();
        var sale_value = $('.separateSaleForm .sale_value').val();
        var note = $('.separateSaleForm .note').val();
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
                sale_table.ajax.reload(null, false);
            }
        });
        $('#separateSaleModal').modal('hide');
    });

    $('#sale_table tbody').on('click', '.edit-sale', function () {
        var data_string = sale_table.rows($(this).parent('td').parent()).data();
        var account_id = data_string[0]['account_id'];
        var sale_value = data_string[0]['sale_value'];
        var note = data_string[0]['note'];
        $('.modal-title').text('แก้ไข' + ' ' + account_id);
        $('#editSaleModal').modal('show');
        $('#editSaleModal').on('shown.bs.modal', function () {
            $('.account_id').val(account_id);
            $('.sale_value').val(sale_value);
            $('.note').val(note);
            $('.confirm-delete-sale-transaction').attr('data-account_id', account_id);
        });
    });

    $('.editSaleForm').on('submit', function (e) {
        e.preventDefault();
        var account_id = $('.account_id').val();
        var sale_value = $('.editSaleForm .sale_value').val();
        var note = $('.note').val();
        $.ajax({
            type: 'GET',
            url: 'edit-sale-transaction',
            data: {
                account_id: account_id,
                sale_value: sale_value,
                note: note,
            },
            success: function () {
                sale_table.ajax.reload(null, false);
            }
        });
        $('#editSaleModal').modal('hide');
    });

    $('.editSaleForm').on('click', 'button.confirm-delete-sale-transaction', function () {
        var account_id = $('.account_id').val();
        $.ajax({
            type: 'GET',
            url: 'delete-sale-transaction',
            data: {
                account_id: account_id
            },
            success: function () {
                sale_table.ajax.reload(null, false);
            }
        });
        $('#editSaleModal').modal('hide');
    });

    $('#sale_table tbody').on('click', '.edit-separate-sale', function () {
        var data_string = sale_table.rows($(this).parent('td').parent()).data();
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
                sale_table.ajax.reload(null, false);
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
                sale_table.ajax.reload(null, false);
            }
        });
        $('#editseparateSaleModal').modal('hide');
    });

    $("#sale_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});