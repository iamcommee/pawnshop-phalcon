$(function () {

  $('.btn-scroll-to-top').click(function () {
    $(document).scrollTop(0);
    return false;
  });

  $('.btn-scroll-to-bottom').click(function () {
      $(document).scrollTop($(document).height());
      return false;
  });

  $('.status').each(function () {
    var text = $(this).text().trim();
    if (text == "ฝาก" || text == 'ซื้อเข้า') {

    } else if (text == "ต่อดอกชิ้นเดียว" || text == 'ต่อดอกทั้งหมด') {
      $(this).parent().addClass("color-green");
    } else if (text == "ไถ่คืนชิ้นเดียว" || text == 'ไถ่คืนทั้งหมด') {
      $(this).parent().addClass("color-red");
    } else if (text == "เพิ่มเงิน" || text == 'ลดต้น') {
      $(this).parent().addClass("color-green");
    } else if (text == "หลุด") {
      $(this).parent().addClass("color-yello");
    } else if (text == "ตั้งขาย" || text == "ตั้งขายกรณีพิเศษ") {
      $(this).parent().addClass("color-orange");
    } else if (text == "ขายแล้ว") {
      $(this).parent().addClass("color-red");
    } else if (text == "มัดจำ") {
      $(this).parent().addClass("color-purple");
    } else {

    }

  });

  $('.transaction_date').datepicker({
    format: "DD/MM/YYYY",
    language: "th-TH",
    autoHide: "true",
    autoPick: "true",
    zIndex: "2000"
  });

  moment.locale('th');
  $('.transaction_time').val(moment().format('HH:mm'));

  $('.start_date').datepicker({
    format: "DD/MM/YYYY",
    language: "th-TH",
    autoHide: "true",
    zIndex: "2000"
  });

  $('.end_date').datepicker({
    format: "DD/MM/YYYY",
    language: "th-TH",
    autoHide: "true",
    zIndex: "2000"
  });

  $(".btn-submit").click(function () {
    $.ajax({
      success: function () {
        window.location = '../';
      }
    });
  });

  $("#idcard").click(function () {
    var idcard = $("#idcard").val();
    window.open('../../customer/search/' + idcard);
  });

  // Start transaction

  $('.transaction-form').on('click', 'button.confirm-delete-transaction', function () {
    var btn = $(this).attr('disabled', true);
    var account_id = $(this).data('account_id');
    console.log(account_id);
    $.ajax({
      type: 'GET',
      url: '../delete-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End transaction

  // Start interest transaction

  $('.transaction-form').on('click', 'button.confirm-delete-interests-transaction', function () {
    var btn = $(this).attr('disabled', true);
    var account_id = $(this).parent().parent().data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-interests-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  $('.transaction-form').on('click', 'button.confirm-delete-interest-transaction', function () {
    var btn = $(this).attr('disabled', true);
    var account_id = $(this).parent().parent().data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-interests-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End interest transaction

  // Start withdraw transaction

  $('.transaction-form').on('click', 'button.confirm-delete-withdraws-transaction', function () {
    var btn = $(this).attr('disabled', true);
    var account_id = $(this).parent().parent().data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-withdraws-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  $('.transaction-form').on('click', 'button.confirm-delete-withdraw-transaction', function () {
    var btn = $(this).attr('disabled', true);
    var account_id = $(this).parent().parent().data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-withdraw-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End withdraw transaction

  // Start delete increase transaction

  $('.transaction-form').on('click', 'button.confirm-delete-increase-transaction', function () {
    var account_id = $(this).data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-increase-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete increase transaction

  // Start delete decrease transaction

  $('.transaction-form').on('click', 'button.confirm-delete-decrease-transaction', function () {
    var account_id = $(this).data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-decrease-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete decrease transaction

  // Start delete waiting transaction

  $('.transaction-form').on('click', 'button.confirm-delete-sale-transaction', function () {
    var account_id = $(this).data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-sale-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete waiting transaction

  // Start delete deposit transaction

  $('.transaction-form').on('click', 'button.confirm-delete-deposit-transaction', function () {
    var account_id = $(this).data('account_id');
    console.log(account_id);
    $.ajax({
      type: 'GET',
      url: '../delete-deposit-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete deposit transaction

  // Start delete separate product transaction

  $('.transaction-form').on('click', 'button.confirm-delete-separate-product-transaction', function () {
    var transaction_id = $(this).data('transaction_id');
    $.ajax({
      type: 'GET',
      url: '../delete-separate-product-transaction',
      data: {
        transaction_id: transaction_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete separate product transaction

  // Start delete separate sale transaction

  $('.transaction-form').on('click', 'button.confirm-delete-separate-sale-transaction', function () {
    var account_id = $(this).data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-separate-sale-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete separate sale transaction

  // Start delete sold out transaction

  $('.transaction-form').on('click', 'button.confirm-delete-sold-out-transaction', function () {
    var account_id = $(this).data('account_id');
    $.ajax({
      type: 'GET',
      url: '../delete-sold-out-transaction',
      data: {
        account_id: account_id
      },
      success: function () {
        location.reload(true);
      }
    });
  });

  // End delete sole out transaction

  // Start edit Product 


  $('.productForm').on('submit', function (e) {
    e.preventDefault();
    var product_id = $('.product_id').val();
    var product_name = $('.product_name').val();
    var product_brand = $('.product_brand').val();
    var product_detail = $('.product_detail').val();
    var product_value = $('.product_value').val();
    $.ajax({
      type: 'GET',
      url: '../../product/edit-product-information',
      data: {
        product_id: product_id,
        product_name: product_name,
        product_brand: product_brand,
        product_detail: product_detail,
        product_value: product_value
      },
      success: function () {
        location.reload();
      }
    });
  })

  $('button.confirm-delete-product').on('click', function () {
    var product_id = $(this).data('product_id');
    $.ajax({
      type: 'GET',
      url: '../../product/delete-product-information',
      data: {
        product_id: product_id
      },
      success: function () {
        location.reload();
      }
    });
  });

  // End edit Product

  // Fix search bar
  $("#number").autocomplete({
    source: '../../agreement/getCustomer',
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