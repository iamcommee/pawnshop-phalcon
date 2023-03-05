$(function () {
    $("#search_agreement").autocomplete({
        source: '../agreement/getCustomer',
        minLength: 2,
        select: function (event, ui) {
            $('#number').val(ui.item.idcard);
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append("<div class='custom-jquery-autocomplete'>" + item.label + "</div>")
            .appendTo(ul);
    };
});