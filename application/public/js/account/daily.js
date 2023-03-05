$(function () {

    var url_string = window.location.href;
    var url = new URL(url_string);
    var year = url.searchParams.get("year");
    var month = url.searchParams.get("month");

    if (!year) {
        var date_time = new Date();
        var year = date_time.getFullYear();
    }

    if (!month) {
        var date_time = new Date();
        var month = date_time.getMonth() + 1;
    }

    var account_table = $('#account_table').DataTable({
        ajax: {
            url: 'server_processing/' + year + '/' + month,
            dataSrc: ""
        },
        columns: [{
                data: "date"
            },
            {
                data: "sum_pawn_value"
            },
            {
                data: "sum_withdraw_value"
            },
            {
                data: "sum_sale_value"
            },
            {
                data: "sum_interest_value"
            },
            {
                data: "sum_withdraw_interest_value"
            },
            {
                data: "sum_profit_value"
            },
            {
                data: "sum_selling_value"
            }
        ],
        ordering: false,
        scrollY: 500,
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
            title: '',
            extend: 'print',
            footer: true,
            text: 'ดาวน์โหลดตาราง',
            className: 'btn btn-custom btn-outline-primary x-rounded',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6]
            },
            customize: function ( win ) {
                $(win.document.body).find( 'table' )
                .css( 'color', 'black' );

                $(win.document.body).find( 'td' )
                .css( 'border', '2px solid black' );
            },
        }],
        footerCallback: function () {
            var api = this.api();
            var pawn_value_counter = 0;
            var withdraw_value_counter = 0;
            var sale_value_counter = 0;
            var interest_value_counter = 0;
            var withdraw_interest_value_counter = 0;
            var profit_value_counter = 0;
            var selling_value_counter = 0;


            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i : 0;
            };

            api.columns('.sum').every(function () {
                var sum = this
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                this.footer().innerHTML = $.number(sum);
                // $('tr:eq(1) th:eq(2)', api.table().footer()).html(format(total2, ''));
            });

            var sum_pawn_value = api
                .column(1)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        pawn_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);


            var sum_withdraw_value = api
                .column(2)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        withdraw_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);

            var sum_sale_value = api
                .column(3)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        sale_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);

            var sum_interest_value = api
                .column(4)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        interest_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);

            var sum_withdraw_interest_value = api
                .column(5)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        withdraw_interest_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);

            var sum_profit_value = api
                .column(6)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        profit_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);

            var sum_selling_value = api
                .column(7)
                .data()
                .reduce(function (a, b) {
                    if (intVal(b) > 0) {
                        selling_value_counter++;
                    }
                    return intVal(a) + intVal(b);
                }, 0);

            $('tr:eq(1) th:eq(1)', api.table().footer()).html($.number(Math.round(sum_pawn_value / pawn_value_counter)));
            $('tr:eq(1) th:eq(2)', api.table().footer()).html($.number(Math.round(sum_withdraw_value / withdraw_value_counter)));
            $('tr:eq(1) th:eq(3)', api.table().footer()).html($.number(Math.round(sum_sale_value / sale_value_counter)));
            $('tr:eq(1) th:eq(4)', api.table().footer()).html($.number(Math.round(sum_interest_value / interest_value_counter)));
            $('tr:eq(1) th:eq(5)', api.table().footer()).html($.number(Math.round(sum_withdraw_interest_value / withdraw_interest_value_counter)));
            $('tr:eq(1) th:eq(6)', api.table().footer()).html($.number(Math.round(sum_profit_value / profit_value_counter)));
            $('tr:eq(1) th:eq(7)', api.table().footer()).html($.number(Math.round(sum_selling_value / selling_value_counter)));


            // console.log(avg_withdraw_value);
            // api.columns('.sum').every(function () {
            //     var counter = 0;
            //     var sum = this
            //         .data()
            //         .reduce(function (a, b) {
            //             if (intVal(b) > 0) {
            //                 counter++;
            //             }
            //         }, 0);

            //     var monTotal = api
            //         .column(1)
            //         .data()
            //         .reduce(function (a, b) {
            //             return intVal(a) + intVal(b);
            //         }, 0);

            //     console.log(counter);

            // });
        },
        initComplete: function () {
            $('.account_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            account_table.columns.adjust();
        }
    });

    $("#account_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});