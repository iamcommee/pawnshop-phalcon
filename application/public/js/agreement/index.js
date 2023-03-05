$(function () {

    getMaxAgreementNumber();
    var AgreementNumberUpdate = setInterval(getMaxAgreementNumber, 3000);

    $('#start_date').datepicker({
        format: "D/M/YYYY",
        language: "th-TH",
        autoHide: "true",
        autoPick: "true"
    });

    $('#end_date').ready(function () {
        getNextMonth();
    });

    $('#start_date').on('change', function () {
        getNextMonth();
    });

    $('#start_date').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            var date = $(this).datepicker('getDate', true);
            $(this).val(date);
            getNextMonth();
        }
    });

    $('#end_date').datepicker({
        format: "D/M/YYYY",
        language: "th-TH",
        autoHide: "true"
    });

    $('#end_date').on('change', function () {
        getPrevMonth();
    });

    $('#end_date').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            var date = $(this).datepicker('getDate', true);
            $(this).val(date);
            getPrevMonth();
        }
    });

    $("#idcard").keyup(function () {
        var idcard = $(this).val();
        if (idcard == "") {
            $('#idcard_image').attr("src", "../img/blank.jpg");
        } else {
            $.ajax({
                dataType: "json",
                url: "getCustomerImg?term=" + idcard,
                success: function (data) {
                    $('#idcard_image').attr("src", "../customerimg/" + data.image);
                }
            });
        }
    });

    $("#idcard").autocomplete({
        source: 'getCustomer',
        minLength: 2,
        select: function (event, ui) {
            $('#firstname').val(ui.item.firstname);
            $('#lastname').val(ui.item.lastname);
            $('#idcard_image').attr("src", "../customerimg/" + ui.item.image);
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append("<div class='custom-jquery-autocomplete'>" + item.label + "</div>")
            .appendTo(ul);
    };

    $(".custom-file-input").on("change", function () {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        $('#idcard_image').attr("src", "../customerimg/" + fileName);
        $('.customerimg').val(fileName);
    });

    $(".product_name").autocomplete({
        source: 'getProduct',
        minLength: 2,
        select: function (event, ui) {
            var selector = $(this).parent('td').parent();
            $(selector.children('td').children('.product_name')).val(ui.item.product_name);
            $(selector.children('td').children('.product_brand')).val(ui.item.product_brand);
            $(selector.children('td').children('.product_detail')).val(ui.item.product_detail);
        },
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                console.log('test');
                return $("<li>")
                    .append("<div class='custom-jquery-autocomplete'>" + item.label + "</div>")
                    .appendTo(ul);
            };
        }
    });

    $("#agreement_number").click(function () {
        clearInterval(AgreementNumberUpdate);
    });

    $('.search-button').click(function () {
        var number = $('#idcard').val();
        window.open("../customer/search/" + number);
    });

    $('.load-last-image-button').click(function () {
        $.ajax({
            dataType: "json",
            url: "getLatestFile",
            success: function (data) {
                $('.customerimg').val(data.latest_file);
                $('.custom-file-input').siblings(".custom-file-label").addClass("selected").html(data.latest_file);
                $('#idcard_image').attr("src", "../customerimg/" + data.latest_file);
            }
        });
    });

});

function getMaxAgreementNumber() {
    $.ajax({
        dataType: "json",
        url: "getMaxAgreementNumber",
        success: function (data) {
            $('#agreement_number').val(data.max_agreement_number);
        }
    });
}

function getNextMonth() {

    var start_date = $("#start_date").val();
    var start_date_array = start_date.split('/'); // แยก array  0 วัน 1 เดือน  2 ปี
    // console.log(start_date_array);
    var start_date = new Date(start_date_array[2], (start_date_array[1] - 1), start_date_array[0]);
    // console.log(start_date);

    var temp = new Date(start_date_array[2], (start_date_array[1] - 1), start_date_array[0]);
    var nextMonth = new Date(temp.setMonth(temp.getMonth() + 1));
    // console.log(temp);

    // console.log(daysInThisMonth(start_date));
    // console.log(daysInThisMonth(nextMonth));

    if (daysInThisMonth(start_date) == daysInThisMonth(nextMonth)) {

        if (start_date.getMonth() + 1 == 1 && nextMonth.getMonth() + 1 == 3) { // เดือน 1 มี 31 วัน, เดือน 2 มี 28,29 วัน
            var now = new Date(start_date);
            var d = new Date(now.getFullYear(), 2, 0).getDate();
            var m = nextMonth.getMonth();
            var y = nextMonth.getFullYear();

            var end_date = d + "/" + m + "/" + y;
        } else if (start_date.getMonth() + 1 == 3 && nextMonth.getMonth() + 1 == 5) { // เดือน 3 มี 31 วัน, เดือน 4 มี 30 วัน
            var d = 30;
            var m = nextMonth.getMonth();
            var y = nextMonth.getFullYear();

            var end_date = d + "/" + m + "/" + y;
        } else if (start_date.getMonth() + 1 == 5 && nextMonth.getMonth() + 1 == 7) { // เดือน 5 มี 31 วัน, เดือน 6 มี 30 วัน
            var d = 30;
            var m = nextMonth.getMonth();
            var y = nextMonth.getFullYear();

            var end_date = d + "/" + m + "/" + y;
        } else if (start_date.getMonth() + 1 == 8 && nextMonth.getMonth() + 1 == 10) {  // เดือน 8 มี 31 วัน, เดือน 9 มี 30 วัน
            var d = 30;
            var m = nextMonth.getMonth();
            var y = nextMonth.getFullYear();

            var end_date = d + "/" + m + "/" + y;
        } else if (start_date.getMonth() + 1 == 10 && nextMonth.getMonth() + 1 == 12) { // เดือน 10 มี 31 วัน, เดือน 11 มี 30 วัน
            var d = 30;
            var m = nextMonth.getMonth();
            var y = nextMonth.getFullYear();

            var end_date = d + "/" + m + "/" + y;
        } else {
            var d = nextMonth.getDate();
            var m = nextMonth.getMonth() + 1;
            var y = nextMonth.getFullYear();

            var end_date = d + "/" + m + "/" + y;
        }


    } else {
        var d = nextMonth.getDate();
        var m = nextMonth.getMonth() + 1;
        var y = nextMonth.getFullYear();

        var end_date = d + "/" + m + "/" + y;
    }


    $("#end_date").val(end_date);

}

function getPrevMonth() {
    // nothing 
}

function daysInThisMonth(date) {
    var now = new Date(date);
    return new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
}