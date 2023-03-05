$(function () {

    var product_table = $('#product_table').DataTable({
        ajax: {
            url: "server_processing/all",
            dataSrc: ""
        },
        columns: [{
                data: {
                    product_id: "product_id",
                    agreement_number: "link",
                },
                render: function (data) {
                    return data['link'];
                }
            },
            {
                data: "product_id"
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
                data: "status"
            },
            {
                data: {
                    product_id: "product_id",
                },
                render: function (data) {
                    return '<span class="edit-product color-black button-click" product_value="' + data.product_id + '"> แก้ไข </span>';
                }
            }
        ],
        lengthChange: false,
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
        select: {
            style: 'os',
            selector: 'td'
        },
        buttons: [{
            extend: 'excelHtml5',
            customize: function (xlsx) {
                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                $('row c[r^="A"]', sheet).each(function () {
                    $(this).attr('s', '12');
                });
            },
            text: 'ดาวน์โหลดตาราง',
            className: 'btn btn-custom btn-outline-primary x-rounded',
            title: '',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6],
                format: {
                    header: function (data, columnIdx) {
                        if (columnIdx == 2) {
                            return 'สินค้า';
                        } else if (columnIdx == 3) {
                            return 'ยี่ห้อ';
                        } else if (columnIdx == 4) {
                            return 'รายละเอียด';
                        } else if (columnIdx == 6) {
                            return 'สถานะ';
                        } else {
                            return data;
                        }
                    }
                }
            }
        }, {
            text: 'แก้ไขหลายรายการ',
            className: 'btn btn-custom btn-outline-primary x-rounded edit-multi-product',
            action: function () {
                var product_list = product_table.rows('.selected').data();
                var product_list_array = [];
                for (var i = 0; i < product_list.length; i++) {
                    var product_list_string = product_list[i]['product_id'];
                    // เปลี่ยนเครื่องหมาย + ใน url
                    var product_id = product_list_string.replace(new RegExp("\\+", "g"), "%2B");
                    product_list_array.push(product_id);
                }
                var product_list_json = JSON.stringify(product_list_array);
                console.log(product_list_json);
                $('#editMultiProductModal').modal('show');
                $('.product_list').val(product_list_json);

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
            $('.product_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            product_table.columns.adjust();
        }
    });

    $("#product_name_filter").autocomplete({
        source: 'getProduct',
        minLength: 2,
        select: function (event, ui) {
            $('#product_name_filter').val(ui.item.name);
            product_table.ajax.url('server_processing/' + ui.item.name).load();
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append("<div class='custom-jquery-autocomplete'>" + item.label + "</div>")
            .appendTo(ul);
    };

    $(".btn-redo").click(function () {
        product_table.ajax.url('server_processing/all').load();
    });

    yadcf.init(product_table, [{
        column_number: 2,
        filter_type: 'multi_select',
        filter_default_label: 'ค้นหาสินค้า',
        select_type: 'select2',
        select_type_options: {
            width: '140px',
        },
        filter_reset_button_text: false,
        filter_match_mode: 'exact'
    }, {
        column_number: 3,
        filter_type: 'multi_select',
        filter_default_label: 'ค้นหายี่ห้อ',
        select_type: 'select2',
        select_type_options: {
            width: '140px',
        },
        filter_reset_button_text: false,
        filter_match_mode: 'exact'
    }, {
        column_number: 4,
        filter_type: 'multi_select',
        filter_default_label: 'ค้นหารายละเอียด',
        select_type: 'select2',
        select_type_options: {
            width: '140px',
        },
        filter_reset_button_text: false,
        filter_match_mode: 'exact'
    }, {
        column_number: 6,
        filter_type: 'multi_select',
        filter_default_label: 'ค้นหาสถานะ',
        select_type: 'select2',
        select_type_options: {
            width: '140px',
        },
        filter_reset_button_text: false,
        filter_match_mode: 'exact'
    }]);

    $('#product_table tbody').on('click', '.edit-product', function () {
        var data_string = product_table.rows($(this).parent('td').parent()).data();
        var agreement_number = data_string[0]['agreement_number'];
        var product_id = data_string[0]['product_id'];
        var product_name = data_string[0]['product_name'];
        var product_detail = data_string[0]['product_detail'];
        var product_brand = data_string[0]['product_brand'];
        var product_value = data_string[0]['product_value'];
        $('.modal-title').text('แก้ไขข้อมูลสินค้าเลขที่สัญญา' + ' ' + agreement_number);
        $('#productModal').modal('show');
        $('#productModal').on('shown.bs.modal', function () {
            $('.product_id').val(product_id);
            $('.product_name').val(product_name);
            $('.product_brand').val(product_brand);
            $('.product_detail').val(product_detail);
            $('.product_value').val(product_value);
            $('.confirm-delete-product').attr('data-product_id', product_id);
        });
    });

    $('.productForm').on('submit', function (e) {
        e.preventDefault();
        var product_id = $('.product_id').val();
        var product_name = $('.product_name').val();
        var product_brand = $('.product_brand').val();
        var product_detail = $('.product_detail').val();
        var product_value = $('.product_value').val();
        $.ajax({
            type: 'GET',
            url: 'edit-product-information',
            data: {
                product_id: product_id,
                product_name: product_name,
                product_brand: product_brand,
                product_detail: product_detail,
                product_value: product_value
            },
            success: function () {
                product_table.ajax.reload(null, false);
            }
        });
        $('#productModal').modal('hide');
    });

    $('.productForm').on('click', 'button.confirm-delete-product', function () {
        var product_id = $('.product_id').val();
        $.ajax({
            type: 'GET',
            url: 'delete-product-information',
            data: {
                product_id: product_id
            },
            success: function () {
                product_table.ajax.reload(null, false);
            }
        });
        $('#productModal').modal('hide');
    });

    $("#product_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');
});