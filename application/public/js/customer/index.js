$(function () {

    var customer_table = $('#customer_table').DataTable({
        ajax: {
            url: "server_processing",
            dataSrc: "",
        },
        columns: [{
                data: {
                    customer_id: "customer_id",
                    idcard: "idcard",
                    image: "image"
                },
                render: function (data) {
                    return '<a target="_blank" href="search/'+data['idcard'].trim()+'">'+data['idcard'].trim()+'</a>';
                }
            },
            {
                data: "firstname"
            },
            {
                data: "lastname"
            },
            {
                data: {
                    idcard: "idcard",
                },
                render: function (data) {
                    return '<span class="edit-customer color-black button-click" value="' + data.idcard + '"> แก้ไข </span>';
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
        dom: "<'row'<'col-sm-6 col-md-6'><'col-sm-6 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        initComplete: function () {
            $('.customer_table_wrap').removeClass('d-none');
            $('.spinner').addClass('d-none');
            customer_table.columns.adjust();
        }
    });

    $('#customer_table tbody').on('click', '.edit-customer', function () {
        var data_string = customer_table.rows($(this).parent('td').parent()).data();
        var customer_id = data_string[0]['customer_id'];
        var idcard = data_string[0]['idcard'];
        var firstname = data_string[0]['firstname'];
        var lastname = data_string[0]['lastname'];
        var image = data_string[0]['image'];
        $('.modal-title').text('แก้ไขข้อมูลลูกค้า' + ' ' + idcard);
        $('#customerModal').modal('show');
        $('#customerModal').on('shown.bs.modal', function () {
            $('.customer_id').val(customer_id);
            $('.idcard').val(idcard);
            $('.firstname').val(firstname);
            $('.lastname').val(lastname);
            $('.image').val(image);
        });
    });

    $('.customerForm').on('submit', function (e) {
        e.preventDefault();
        var customer_id = $('.customer_id').val();
        var idcard = $('.idcard').val();
        var firstname = $('.firstname').val();
        var lastname = $('.lastname').val();
        var image = $('.image').val();
        $.ajax({
            type: 'GET',
            url: 'edit-customer-information',
            data: {
                customer_id: customer_id,
                idcard: idcard,
                firstname: firstname,
                lastname: lastname,
                image: image
            },
            success: function () {
                customer_table.ajax.reload(null, false);
            }
        });
        $('#customerModal').modal('hide');
    });

    $("#customer_table_filter input.form-control").removeClass('form-control form-control-sm').addClass('custom-filter text-center');

});