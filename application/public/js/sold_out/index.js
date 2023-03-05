$(function () {

    $('.transaction_date').datepicker({
        format: "DD/MM/YYYY",
        language: "th-TH",
        autoHide: "true",
        autoPick: "true",
        zIndex: "2000"
    });

    moment.locale('th');

    var sold_out_table = $('#sold_out_table').DataTable({
        ajax: {
            url: "server_processing",
            dataSrc: ""
        },
        columns: [{
                data: {
                    product_id: "product_id",
                    agreement_nubmer: "agreement_number",
                    common_transaction_date: "common_transaction_date",
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
                data: "sold_value"
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
                    return '<span class="edit-sold-out color-black button-click"> แก้ไข </span>';
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
            $('.sold_out_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            sold_out_table.columns.adjust();
        }
    });

    yadcf.init(sold_out_table, [{
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

    $('#sold_out_table tbody').on('click', '.edit-sold-out', function () {
        var data_string = sold_out_table.rows($(this).parent('td').parent()).data();
        var account_id = data_string[0]['account_id'];
        var transaction_date = data_string[0]['common_transaction_date'];
        var sold_value = data_string[0]['sold_value'];
        var note = data_string[0]['note'];
        $('.modal-title').text('แก้ไข' + ' ' + account_id);
        $('#editSoldOutModal').modal('show');
        $('#editSoldOutModal').on('shown.bs.modal', function () {
            $('.account_id').val(account_id);
            $('.transaction_date').val(transaction_date);
            $('.sold_value').val(sold_value);
            $('.note').val(note);
            $('.confirm-delete-sold-out-transaction').attr('data-account_id', account_id);
        });
    });

    $('.editSoldOutForm').on('submit', function (e) {
        e.preventDefault();
        var account_id = $('.account_id').val();
        var transaction_date = $('.transaction_date').val();
        var sold_value = $('.editSoldOutForm .sold_value').val();
        var note = $('.editSoldOutForm .note').val();
        $.ajax({
            type: 'GET',
            url: 'edit-sold-out-transaction',
            data: {
                account_id: account_id,
                transaction_date: transaction_date,
                sold_value: sold_value,
                note: note,
            },
            success: function () {
                sold_out_table.ajax.reload(null, false);
            }
        });
        $('#editSoldOutModal').modal('hide');
    });


    $('.editSoldOutForm').on('click', 'button.confirm-delete-sold-out-transaction', function () {
        var account_id = $('.account_id').val();
        $.ajax({
            type: 'GET',
            url: 'delete-sold-out-transaction',
            data: {
                account_id: account_id
            },
            success: function () {
                sold_out_table.ajax.reload(null, false);
            }
        });
        $('#editSoldOutModal').modal('hide');
    });

    $("#sold_out_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});