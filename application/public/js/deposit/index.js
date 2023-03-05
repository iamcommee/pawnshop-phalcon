$(function () {

    $('.transaction_date').datepicker({
        format: "DD/MM/YYYY",
        language: "th-TH",
        autoHide: "true",
        zIndex: "2000"
    });

    moment.locale('th');

    var deposit_table = $('#deposit_table').DataTable({
        ajax: {
            url: "server_processing",
            dataSrc: ""
        },
        columns: [{
                data: {
                    product_id: "product_id",
                    agreement_nubmer: "agreement_number",
                    deposit_transactions: "deposit_transactions",
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
                data: "sale_value"
            },
            {
                data: "sum_deposit_value"
            },
            {
                data: "transaction_date"
            },
            {
                data: {
                    agreement_nubmer: "agreement_nubmer",
                },
                render: function (data) {
                    return '<span class="deposit-detail color-black button-click" value="' + data.agreement_nubmer + '"> รายละเอียด </span>';
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
                var sale_list_ = deposit_table.rows('.selected').data();
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
            text: 'ชำระมัดจำ',
            className: 'depositButton btn btn-custom btn-outline-primary x-rounded',
            action: function () {
                var deposit_list_ = deposit_table.rows('.selected').data();
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
                columns: [0, 1, 2, 3, 4, 5, 6],
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
            $('.deposit_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            deposit_table.columns.adjust();
        }
    });

    yadcf.init(deposit_table, [{
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

    $('#deposit_table tbody').on('click', 'tr', function () {
        $(this).toggleClass('selected');
    });

    $("#deposit_table tbody").on('click', '.deposit-detail', function () {
        if ($(this).parent('td').parent().hasClass('selected')) {
            $(this).parent('td').parent().toggleClass('selected');
        }
        $(this).parent('td').parent().toggleClass('selected');
        var row = deposit_table.row($(this).parent('td').parent());
        if (row.child.isShown()) {
            row.child.hide();
        } else {
            row.child(format(row.data()), 'detail').show();
        }
    });

    // prevent onclick event
    $('#deposit_table tbody').on('click', 'tr.detail', function () {
        $(this).toggleClass('selected');
        if ($(this).hasClass('selected')) {
            $(this).toggleClass('selected');
        }
    });

    $('#depositModal').on('show.bs.modal', function (e) {
        var account_id = $(e.relatedTarget).data('account_id');
        $.ajax({
            type: "GET",
            url: "get-deposit-info/" + account_id,
            complete: function (data) {
                var deposit_info = JSON.parse(data.responseText);
                $('.modal-title').text('แก้ไขมัดจำ');
                $('.account_id').val(deposit_info.account_id);
                $('.transaction_date').val(deposit_info.transaction_date);
                $('.transaction_time').val(deposit_info.transaction_time);
                $('.deposit_value').val(deposit_info.deposit_value);
                $('.note').val(deposit_info.note);
            }
        });
    });

    $('.depositForm').on('submit', function (e) {
        e.preventDefault();
        var account_id = $('.account_id').val();
        var transaction_date = $('.transaction_date').val();
        var transaction_time = $('.transaction_time').val();
        var deposit_value = $('.deposit_value').val();
        var note = $('.note').val();
        $.ajax({
            type: 'GET',
            url: 'edit-deposit-transaction',
            data: {
                account_id: account_id,
                transaction_date: transaction_date,
                transaction_time: transaction_time,
                deposit_value: deposit_value,
                note: note
            },
            success: function () {
                deposit_table.ajax.reload(null, false);
            }
        });
        $('#depositModal').modal('hide');
    });

    $('.depositForm').on('click', '.confirm-delete-deposit-transaction', function (e) {
        e.preventDefault();
        var account_id = $('.account_id').val();
        $.ajax({
            type: 'GET',
            url: 'delete-deposit-transaction',
            data: {
                account_id: account_id,
            },
            success: function () {
                deposit_table.ajax.reload(null, false);
            }
        });
        $('#depositModal').modal('hide');
    });

    function format(d) {
        return '<table class="table table-borderless">' + transactionString(d) + '</table>';
    }

    function transactionString(d) {
        var str = "";
        var j = 1;
        for (var i = 0; i < d.deposit_transactions.length; i++) {
            str += '<tr class="detail text-center">\
            <td style="width:30%"> มัดจำครั้งที่  ' + j + ' - ' + d.deposit_transactions[i]['deposit_value'] + '</td>\
            <td style="width:30%"> วันที่ทำรายการ ' + d.deposit_transactions[i]['transaction_date'] + '</td>\
            <td style="width:30%"> หมายเหตุ ' + d.deposit_transactions[i]['note'] + '</td>\
            <td style="width:10%"><span class="color-black button-click" data-toggle="modal" data-target="#depositModal" data-account_id="' + d.deposit_transactions[i]['account_id'] + '"> แก้ไข </span></td>\
            </tr>';
            j++;
        }
        return str;
    }

    $("#deposit_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});