$(function () {

    var pathArray = window.location.pathname.split( '/' );
    var url;
    if(pathArray.length == 4){
        url = '../agreement/getCustomer';
    } else if (pathArray.length == 5){
        url = '../../agreement/getCustomer';
    }
    
    $("#sidebar").mCustomScrollbar({
        theme: "minimal"
    });

    $('.overlay-bg').on('click', function () {
        $('#sidebar').removeClass('active');
        $('.overlay-bg').removeClass('active');
    });

    $('#sidebar-collapse').on('click', function () {
        $('#sidebar').addClass('active');
        $('.overlay-bg').addClass('active');
        $('.collapse.in').toggleClass('in');
        $('a[aria-expanded=true]').attr('aria-expanded', 'false');
    });

    $("#number").autocomplete({
        source: url,
        minLength: 2,
        select: function (event, ui) {
            $('#number').val(ui.item.idcard);
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append("<div class='custom-jquery-autocomplete'>" + item.label + "</div>")
            .appendTo(ul);
    };


    $('.custom-search-submit-button').on('click', function () {
        if ($('.custom-search-input').val().trim()) {
            $('.custom-search-form').submit();
        } else if ($('.custom-search-input').val() == '') {
            // nothing
        } else {
            // nothing
        }
    });

    $('.custom-search-input').on('keypress', function (e) {
        if (e.which == 13 && $('.custom-search-input').val().trim() == '') {
            e.preventDefault();
        }
    });

    // $('input').attr('autocomplete', 'off');
});