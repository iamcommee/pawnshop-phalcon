$(function () {
    var transaction_table = $('#transaction_table').DataTable({
        ajax: {
            url: 'server_processing',
            dataSrc: ""
        },
        columns: [{
                data: {
                    agreement_number: "agreement_number"
                },
                render: function (data) {
                    return data["agreement_number"];
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
                data: "status"
            },
            {
                data: "transaction_date"
            },
            {
                data: {
                    idcard: "idcard"
                },
                render: function (data) {
                    return data["idcard"];
                }
            },
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
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                format: {
                    header: function (data, columnIdx) {
                        if (columnIdx == 1) {
                            return 'สินค้า';
                        } else if (columnIdx == 2) {
                            return 'ยี่ห้อ';
                        } else if (columnIdx == 3) {
                            return 'รายละเอียด';
                        } else if (columnIdx == 7) {
                            return 'สถานะ';
                        } else {
                            return data;
                        }
                    }
                }
            }
        }],
        rowCallback: function (row, data) {
            if (data.status == "ฝาก" || data.status == 'ซื้อเข้า') {

            } else if (data.status == "ต่อดอกชิ้นเดียว" || data.status == 'ต่อดอกทั้งหมด') {
                $(row).addClass("color-green");
            } else if (data.status == "ไถ่คืนชิ้นเดียว" || data.status == 'ไถ่คืนทั้งหมด') {
                $(row).addClass("color-red");
            } else if (data.status == "เพิ่มเงิน" || data.status == 'ลดต้น') {
                $(row).addClass("color-green");
            } else if (data.status == "หลุด") {
                $(row).addClass("color-yello");
            } else if (data.status == "ตั้งขาย" || data.status == "ตั้งขายกรณีพิเศษ") {
                $(row).addClass("color-orange");
            } else if (data.status == "ขายแล้ว") {
                $(row).addClass("color-red");
            } else if (data.status == "มัดจำ") {
                $(row).addClass("color-purple");
            } else {

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
            $('.transaction_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            transaction_table.columns.adjust();
        }
    });

    yadcf.init(transaction_table, [{
            column_number: 1,
            filter_type: 'text',
            text_data_delimiter: ' ',
            filter_default_label: 'ค้นหาสินค้า',
            filter_reset_button_text: false
        }, {
            column_number: 2,
            filter_type: 'text',
            text_data_delimiter: ' ',
            filter_default_label: 'ค้นหายี่ห้อ',
            filter_reset_button_text: false
        }, {
            column_number: 3,
            filter_type: 'text',
            text_data_delimiter: ' ',
            filter_default_label: 'ค้นหารายละเอียด',
            filter_reset_button_text: false
        },
        {
            column_number: 7,
            filter_type: 'multi_select',
            filter_default_label: 'ค้นหาสถานะ',
            select_type: 'select2',
            select_type_options: {
                width: '140px',
            },
            filter_reset_button_text: false,
            filter_match_mode: 'exact'
        }
    ]);

    $("#transaction_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});