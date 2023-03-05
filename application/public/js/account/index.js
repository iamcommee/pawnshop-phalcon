$(function () {
    $('#start_date').datepicker({
        format: "D/M/YYYY",
        language: "th-TH",
        autoHide: "true",
    });
    $('#end_date').datepicker({
        format: "D/M/YYYY",
        language: "th-TH",
        autoHide: "true"
    });

    var account_table = $('#account_table').DataTable({
        ordering: false,
        stateSave: true,
        scrollY: 550,
        deferRender: true,
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
            extend: 'pdf',
            pageSize: 'A4',
            orientation: 'landscape',
            className: "btn btn-custom btn-outline-primary x-rounded",
            text: "ดาวน์โหลดตาราง",
            exportOptions: {
                // opitional
                format: {
                    body: function(data) {
                      data = data.replace(/<br\s*\/?>/ig, "\n");
                      data = data.replace(/<hr\s*\/?>/ig, "*********************\n");
                      return data;
                    }
                  }
            },
            customize: function (doc) {
                var objLayout = {};
                objLayout['hLineWidth'] = function (i) {
                    return .5;
                };
                objLayout['vLineWidth'] = function (i) {
                    return .5;
                };
                objLayout['hLineColor'] = function (i) {
                    return '#aaa';
                };
                objLayout['vLineColor'] = function (i) {
                    return '#aaa';
                };
                objLayout['paddingLeft'] = function (i) {
                    return 4;
                };
                objLayout['paddingRight'] = function (i) {
                    return 4;
                };
                doc.content[1].layout = objLayout;
                doc.styles.tableHeader.fontSize = 14;
                doc.defaultStyle.fontSize = 12;
                doc.styles.tableBodyOdd.alignment = 'center';
                doc.styles.tableBodyEven.alignment = 'center';
                doc.content[1].table.widths = [50, 100, 80, 127, 80, 80, 50, 127]
            }
        }],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(),
                data;

            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/.*รวมเป็นเงิน : */, '').replace(/[\$,]/g, '') * 1 :
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
            });
        },
        initComplete: function () {
            $('.account_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
        }
    });

    account_table.columns.adjust();
    $("#account_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});