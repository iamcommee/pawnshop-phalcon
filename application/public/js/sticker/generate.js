$(function () {
    $(".product").autocomplete({
        source: '../getProduct',
        minLength: 1,
        select: function (event, ui) {
            var selector = $(this).parent();
            $(selector.children('.product')).val(ui.item.product);
            $(selector.children('.agreement_number')).val(ui.item.agreement_number);
            $(selector.children('.product_id')).val(ui.item.product_id);
        },
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li>")
                    .append("<div class='custom-jquery-autocomplete'>" + item.label + "</div>")
                    .appendTo(ul);
            }
        }
    });
});