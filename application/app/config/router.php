<?php

$router = $di->getRouter();

// Start Waiting Route

$router->add(
    '/main/move-to-sell/:params',
    [
        'controller'=> 'main',
        'action'    => 'moveToSell',
        'params'    => 1,
    ]
);

// End Waiting Route

// Start Sticker Route

$router->add(
    '/sticker/print-sticker/:params',
    [
        'controller' => 'sticker',
        'action'     => 'printSticker',
        'params'     => 1,
    ]
);

// End Sticker Route

// Start Payment Route

$router->add(
    '/payment/delete-agreement',
    [
        'controller' => 'payment',
        'action'     => 'deleteAgreement',
    ]
);

$router->add(
    '/payment/print-receipt/:params',
    [
        'controller' => 'payment',
        'action'     => 'printReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/payment/print-agreement/:params',
    [
        'controller' => 'payment',
        'action'     => 'printAgreement',
        'params'     => 1,
    ]
);

$router->add(
    '/payment/print-last-receipt/:params',
    [
        'controller' => 'payment',
        'action'     => 'printLastReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/payment/insert-status',
    [
        'controller' => 'payment',
        'action'     => 'insertStatus',
    ]
);

$router->add(
    '/payment/insert-product',
    [
        'controller' => 'payment',
        'action'     => 'insertProduct',
    ]
);

$router->add(
    '/payment/separate-product',
    [
        'controller'=> 'payment',
        'action'    => 'separateProduct',
    ]
);

$router->add(
    '/payment/separate-sale',
    [
        'controller'=> 'payment',
        'action'    => 'separateSale',
    ]
);

$router->add(
    '/payment/edit-separate-sale-transaction',
    [
        'controller'=> 'payment',
        'action'    => 'editSeparateSaleTransaction',
    ]
);

$router->add(
    '/payment/pay-interest-products',
    [
        'controller' => 'payment',
        'action'     => 'payInterestProducts',
    ]
);

$router->add(
    '/payment/pay-interest-product',
    [
        'controller' => 'payment',
        'action'     => 'payInterestProduct',
    ]
);


$router->add(
    '/payment/edit-pawn-transaction',
    [
        'controller' => 'payment',
        'action'     => 'editPawnTransaction',
    ]
);

$router->add(
    '/payment/edit-transaction',
    [
        'controller' => 'payment',
        'action'     => 'editTransaction',
    ]
);

$router->add(
    '/payment/delete-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteTransaction',
    ]
);

$router->add(
    '/payment/edit-interest-transaction',
    [
        'controller' => 'payment',
        'action'     => 'editInterestTransaction',
    ]
);

$router->add(
    '/payment/delete-interests-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteInterestsTransaction',
    ]
);

$router->add(
    '/payment/delete-interest-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteInterestTransaction',
    ]
);

$router->add(
    '/payment/withdraw-products',
    [
        'controller' => 'payment',
        'action'     => 'withdrawProducts',
    ]
);

$router->add(
    '/payment/withdraw-product',
    [
        'controller' => 'payment',
        'action'     => 'withdrawProduct',
    ]
);

$router->add(
    '/payment/edit-withdraw-transaction',
    [
        'controller' => 'payment',
        'action'     => 'editWithdrawTransaction',
    ]
);

$router->add(
    '/payment/delete-withdraws-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteWithdrawsTransaction',
    ]
);

$router->add(
    '/payment/delete-withdraw-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteWithdrawTransaction',
    ]
);

$router->add(
    '/payment/increase-value',
    [
        'controller' => 'payment',
        'action'     => 'increaseValue',
    ]
);

$router->add(
    '/payment/delete-increase-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteIncreaseTransaction',
    ]
);

$router->add(
    '/payment/decrease-value',
    [
        'controller' => 'payment',
        'action'     => 'decreaseValue',
    ]
);

$router->add(
    '/payment/delete-decrease-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteDecreaseTransaction',
    ]
);


$router->add(
    '/payment/edit-sale-transaction',
    [
        'controller'=> 'payment',
        'action'    => 'editSaleTransaction',
    ]
);

$router->add(
    '/payment/delete-sale-transaction',
    [
        'controller'=> 'payment',
        'action'    => 'deleteSaleTransaction',
    ]
);

$router->add(
    '/payment/edit-deposit-transaction',
    [
        'controller' => 'payment',
        'action'     => 'editDepositTransaction',
    ]
);

$router->add(
    '/payment/delete-deposit-transaction/:params',
    [
        'controller' => 'payment',
        'action'     => 'deleteDepositTransaction',
        'params'     => 1,
    ]
);

$router->add(
    '/payment/delete-separate-product-transaction/:params',
    [
        'controller' => 'payment',
        'action'     => 'deleteSeparateProductTransaction',
        'params'     => 1,
    ]
);

$router->add(
    '/payment/delete-separate-sale-transaction/:params',
    [
        'controller' => 'payment',
        'action'     => 'deleteSeparateSaleTransaction',
        'params'     => 1,
    ]
);

$router->add(
    '/payment/edit-sold-out-transaction',
    [
        'controller' => 'payment',
        'action'     => 'editSoldOutTransaction',
    ]
);

$router->add(
    '/payment/delete-sold-out-transaction',
    [
        'controller' => 'payment',
        'action'     => 'deleteSoldOutTransaction',
    ]
);

// End Payment Route

// Start Waiting Route

$router->add(
    '/waiting/move-to-sell/:params',
    [
        'controller'=> 'waiting',
        'action'    => 'moveToSell',
        'params'    => 1,
    ]
);

// End Waiting Route


// Start Sale Route

$router->add(
    '/sale/edit-sale-transaction',
    [
        'controller'=> 'sale',
        'action'    => 'editSaleTransaction',
    ]
);

$router->add(
    '/sale/delete-sale-transaction',
    [
        'controller'=> 'sale',
        'action'    => 'deleteSaleTransaction',
    ]
);

$router->add(
    '/sale/edit-separate-sale-transaction',
    [
        'controller'=> 'sale',
        'action'    => 'editSeparateSaleTransaction',
    ]
);

$router->add(
    '/sale/delete-separate-sale-transaction',
    [
        'controller'=> 'sale',
        'action'    => 'deleteSeparateSaleTransaction',
    ]
);

$router->add(
    '/sale/separate-sale',
    [
        'controller'=> 'sale',
        'action'    => 'separateSale',
    ]
);

$router->add(
    '/sale/create-sale-receipt/:params',
    [
        'controller'=> 'sale',
        'action'    => 'createSaleReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/sale/print-sale-receipt/:params',
    [
        'controller'=> 'sale',
        'action'    => 'printSaleReceipt',
        'params'    => 1,
    ]
);

$router->add(
    '/sale/create-deposit-receipt/:params',
    [
        'controller'=> 'sale',
        'action'    => 'createDepositReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/sale/print-deposit-receipt/:params',
    [
        'controller'=> 'sale',
        'action'    => 'printDepositReceipt',
        'params'    => 1,
    ]
);

// End Sale Route

// Start Deposit Route

$router->add(
    '/deposit/get-deposit-info/:params',
    [
        'controller'=> 'deposit',
        'action'    => 'getDepositInfo',
        'params'    => 1,
    ]
);

$router->add(
    '/deposit/edit-deposit-transaction',
    [
        'controller'=> 'deposit',
        'action'    => 'editDepositTransaction',
    ]
);

$router->add(
    '/deposit/delete-deposit-transaction',
    [
        'controller'=> 'deposit',
        'action'    => 'deleteDepositTransaction',
    ]
);

$router->add(
    '/deposit/create-sale-receipt/:params',
    [
        'controller'=> 'deposit',
        'action'    => 'createSaleReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/deposit/print-sale-receipt/:params',
    [
        'controller'=> 'deposit',
        'action'    => 'printSaleReceipt',
        'params'    => 1,
    ]
);

$router->add(
    '/deposit/create-deposit-receipt/:params',
    [
        'controller'=> 'deposit',
        'action'    => 'createDepositReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/deposit/print-deposit-receipt/:params',
    [
        'controller'=> 'deposit',
        'action'    => 'printDepositReceipt',
        'params'    => 1,
    ]
);

// End Deposit Route

// Start Separate_sale Route

$router->add(
    '/separate_sale/separate-sale',
    [
        'controller'=> 'separate_sale',
        'action'    => 'separateSale',
    ]
);

$router->add(
    '/separate_sale/edit-separate-sale-transaction',
    [
        'controller'=> 'separate_sale',
        'action'    => 'editSeparateSaleTransaction',
    ]
);

$router->add(
    '/separate_sale/delete-separate-sale-transaction',
    [
        'controller'=> 'separate_sale',
        'action'    => 'deleteSeparateSaleTransaction',
    ]
);

$router->add(
    '/separate_sale/create-sale-receipt/:params',
    [
        'controller'=> 'separate_sale',
        'action'    => 'createSaleReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/separate_sale/print-sale-receipt/:params',
    [
        'controller'=> 'separate_sale',
        'action'    => 'printSaleReceipt',
        'params'    => 1,
    ]
);

$router->add(
    '/separate_sale/create-deposit-receipt/:params',
    [
        'controller'=> 'separate_sale',
        'action'    => 'createDepositReceipt',
        'params'     => 1,
    ]
);

$router->add(
    '/separate_sale/print-deposit-receipt/:params',
    [
        'controller'=> 'separate_sale',
        'action'    => 'printDepositReceipt',
        'params'    => 1,
    ]
);

// End Separate_sale Route

// Start Customer Rotte

$router->add(
    '/customer/edit-customer-information',
    [
        'controller' => 'customer',
        'action'     => 'editCustomerInformation',
    ]
);

// End Customer Route

// Start Setting Rotte

$router->add(
    '/setting/edit-owner-information',
    [
        'controller' => 'setting',
        'action'     => 'editOwnerInformation',
    ]
);

$router->add(
    '/setting/insert-status',
    [
        'controller' => 'setting',
        'action'     => 'insertStatus',
    ]
);

$router->add(
    '/setting/edit-status',
    [
        'controller' => 'setting',
        'action'     => 'editStatus',
    ]
);

$router->add(
    '/setting/delete-status',
    [
        'controller' => 'setting',
        'action'     => 'deleteStatus',
    ]
);

// End Setting Route

// Start Product Route

$router->add(
    '/product/edit-product-information',
    [
        'controller' => 'product',
        'action'     => 'editProductInformation',
    ]
);

$router->add(
    '/product/delete-product-information',
    [
        'controller'=> 'product',
        'action'    => 'deleteProductInformation',
    ]
);

$router->add(
    '/product/edit-multi-product',
    [
        'controller' => 'product',
        'action'     => 'editMultiProduct'
    ]
);

// End Product Route

// Start Sold out Route

$router->add(
    '/sold_out/edit-sold-out-transaction',
    [
        'controller'=> 'soldout',
        'action'    => 'editSoldOutTransaction',
    ]
);

$router->add(
    '/sold_out/delete-sold-out-transaction',
    [
        'controller'=> 'soldout',
        'action'    => 'deleteSoldOutTransaction',
    ]
);

// End Sold out Route


$router->handle();
