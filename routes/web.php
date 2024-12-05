<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('fetch-district-data', function () {
    $cities = App\Models\Master\City\City::paginate(50);

    \DB::beginTransaction();
    foreach ($cities as $city) {
        $cityId = $city->id;
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city={$cityId}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "key: 7529ad345008c0f3471128cc42eff255"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) { echo "cURL Error #:" . $err; return; }

        $response = json_decode($response);
        $results = $response->rajaongkir->results;

        foreach ($results as $result) {
            App\Models\Master\City\District::create([
                'id' => $result->subdistrict_id,
                'name' => $result->subdistrict_name,
                'type' => $result->type,
                'city_id' => $result->city_id
            ]);
        }

    }

    \DB::commit();
    return 'fetch-disctrict-data page <a href="' . $cities->nextPageUrl() . '"> next </a>';
});

Route::group(['namespace' => 'APIMobile'], function () {
  //confirmation
  Route::get('/customer/verification/{verification_code}', 'CustomerController@verificationEmail');
  Route::get('/customer/forget-password/{verification_code}', 'ForgetPasswordController@index');
  Route::get('/customer/forget-password', 'ForgetPasswordController@index');
  Route::post('/customer/forget-password', 'ForgetPasswordController@ProcessForgotPassword');
});

Route::group(['middleware' => ['auth', 'access.application']], function () {
  Route::post('/inventoryDatatable', 'HomeController@inventoryDatatable');
  Route::get('/', 'HomeController@index');
  Route::get('/home', 'HomeController@index');


    Route::group(['prefix' => 'master', 'namespace' => 'Master'], function () {
        Route::post('/banner-mobile/{id}', 'BannerMobileController@update')->middleware('access.menu:master/banner-mobile');
        Route::resource('/banner-mobile', 'BannerMobileController')->middleware('access.menu:master/banner-mobile');

        Route::resource('/branch', 'BranchController');

        Route::resource('/unit', 'UnitController');

        Route::group(['prefix' => 'city', 'namespace' => 'City'], function () {
            Route::resource('/district', 'DistrictController')->middleware('access.menu:master/city/district');
            Route::resource('/city', 'CityController')->middleware('access.menu:master/city/city');
            Route::resource('/province', 'ProvinceController')->middleware('access.menu:master/city/province');
        });

        Route::group(['prefix' => 'material', 'namespace' => 'Material'], function () {
            Route::resource('/color', 'ColorController')->middleware('access.menu:master/material/color');
            Route::resource('/material', 'MaterialController')->middleware('access.menu:master/material/material');
            Route::resource('/raw-material', 'RawMaterialController')->middleware('access.menu:master/material/raw-material');
            // Route::resource('/production-process', 'ProductionProcessController');
        });

        Route::group(['prefix' => 'asset', 'namespace' => 'Asset'], function () {
            Route::resource('/brand', 'AssetBrandController')->middleware('access.menu:master/asset/brand');

            Route::post('/category/{id}', 'AssetCategoryController@update')->middleware('access.menu:master/asset/category');
            Route::resource('/category', 'AssetCategoryController')->middleware('access.menu:master/asset/category');

            Route::post('/asset/{id}', 'AssetController@update')->middleware('access.menu:master/asset/asset');
            Route::resource('/asset', 'AssetController')->middleware('access.menu:master/asset/asset');
        });

        Route::group(['prefix' => 'item', 'namespace' => 'Item'], function () {
            Route::post('/category/{id}', 'ItemCategoryController@update')->middleware('access.menu:master/item/category');
            Route::resource('/category', 'ItemCategoryController')->middleware('access.menu:master/item/category');

            Route::resource('/length', 'ItemLengthController');

            Route::put('/item/image/{id}', 'ItemController@updateImage')->middleware('access.menu:master/ite/itemm');
            Route::delete('/item/image/{id}', 'ItemController@destroyImage')->middleware('access.menu:master/item/item');
            Route::post('/item/{id}', 'ItemController@update')->middleware('access.menu:master/item/item');
            Route::resource('/item', 'ItemController')->middleware('access.menu:master/item/item');
        });

        Route::post("/sales-target/ajaxDataTable", "SalesTargetController@ajaxDataTable")->middleware('access.menu:master/sales-target');
        Route::resource("/sales-target", "SalesTargetController")->middleware('access.menu:master/sales-target');
        Route::resource('/warehouse', 'WarehouseController')->middleware('access.menu:master/warehouse');

        Route::post('/payment/method/{id}', 'Payment\PaymentMethodController@update')->middleware('access.menu:master/payment/method');
        Route::resource('/payment/method', 'Payment\PaymentMethodController')->middleware('access.menu:master/payment/method');

        Route::post('/payment/bank-channel/{id}', 'Payment\PaymentBankChannelController@update')->middleware('access.menu:master/payment/bank-channel');
        Route::resource('/payment/bank-channel', 'Payment\PaymentBankChannelController')->middleware('access.menu:master/payment/bank-channel');

        Route::post('/shipping/cost/{id}', 'Shipping\ShippingCostController@update')->middleware('access.menu:master/shipping/cost');
        Route::resource('/shipping/cost', 'Shipping\ShippingCostController')->middleware('access.menu:master/shipping/cost');

        Route::get('/term-of-service', 'TermOfServiceController@index')->middleware('access.menu:master/term-of-service');
        Route::get('/term-of-service/{id}/edit', 'TermOfServiceController@edit')->middleware('access.menu:master/term-of-service');
        Route::put('/term-of-service/{id}', 'TermOfServiceController@update')->middleware('access.menu:master/term-of-service');

        Route::group(['prefix' => 'customer', 'namespace' => 'Customer'], function () {
            Route::delete('/{id}', 'CustomerController@destroy')->middleware('access.menu:master/customer');
            Route::post('/{id}', 'CustomerController@update')->middleware('access.menu:master/customer');
            Route::get('/{id}/edit', 'CustomerController@edit')->middleware('access.menu:master/customer');
            Route::get('/create', 'CustomerController@create')->middleware('access.menu:master/customer');
            Route::post('/', 'CustomerController@store')->middleware('access.menu:master/customer');
            Route::get('/', 'CustomerController@index')->middleware('access.menu:master/customer');

            Route::post('/company/store', 'CompanyController@store')->middleware('access.menu:master/customer/company');
            Route::resource('/company', 'CompanyController')->middleware('access.menu:master/customer/company');
        });

        Route::group(['prefix' => 'employee', 'namespace' => 'Employee'], function () {
            Route::resource('/role', 'EmployeeRoleController')->middleware('access.menu:master/employee/role');

            Route::get('/{id}/edit', 'EmployeeController@edit')->middleware('access.menu:master/employee');
            Route::post('/{id}', 'EmployeeController@update')->middleware('access.menu:master/employee');
            Route::delete('/{id}', 'EmployeeController@destroy')->middleware('access.menu:master/employee');
            Route::resource('/', 'EmployeeController')->middleware('access.menu:master/employee');
        });
    });

    Route::group(['prefix' => 'mobile', 'namespace' => 'Mobile'], function () {
        Route::group(['prefix' => 'voucher', 'namespace' => 'Voucher'], function () {
            Route::resource('/voucher', 'VoucherController')->middleware('access.menu:mobile/voucher/voucher');
            Route::resource('/usage', 'VoucherUsageController')->middleware('access.menu:mobile/voucher/usage');
        });
    });

    Route::group(['prefix' => 'sales', 'namespace' => 'Sales'], function () {
        Route::get('/quotation/{id}/print', 'SalesQuotationController@print')->middleware('access.menu:sales/quotation');
        Route::get('/quotation/{id}/ajax-form', 'SalesQuotationController@searchById')->middleware('access.menu:sales/quotation');
        Route::get('/quotation/export', 'SalesQuotationController@export')->name("sales.quotation.export")->middleware('access.menu:sales/quotation');
        Route::resource('/quotation', 'SalesQuotationController')->middleware('access.menu:sales/quotation');

        Route::get('/order/{id}/print', 'SalesOrderController@print')->middleware('access.menu:sales/order');
        Route::get('/order/{id}/ajax-form', 'SalesOrderController@searchById')->middleware('access.menu:sales/order');
        Route::get('/order/export', 'SalesOrderController@export')->name("sales.order.export")->middleware('access.menu:sales/order');
        Route::resource('/order', 'SalesOrderController')->names(["index" => "sales.order.index", "show" => "sales.order.show"])->middleware('access.menu:sales/order');

        Route::get('/shipping-instruction/{id}/print', 'ShippingInstructionController@print')->middleware('access.menu:sales/shipping-instruction');
        Route::get('/shipping-instruction/{id}/ajax-form', 'ShippingInstructionController@searchById')->middleware('access.menu:sales/shipping-instruction');
        Route::resource('/shipping-instruction', 'ShippingInstructionController')->middleware('access.menu:sales/shipping-instruction');
        Route::post('/shipping-instruction/{id}/update-status', 'ShippingInstructionController@updateStatus')->name('shipping-instruction.update-status');

        Route::put('/delivery-note/{id}/finish', 'DeliveryNoteController@finish')->middleware('access.menu:sales/delivery-note');
        Route::get('/delivery-note/{id}/print', 'DeliveryNoteController@print')->middleware('access.menu:sales/delivery-note');
        Route::get('/delivery-note/{id}/ajax-form', 'DeliveryNoteController@searchById')->middleware('access.menu:sales/delivery-note');
        Route::resource('/delivery-note', 'DeliveryNoteController')->middleware('access.menu:sales/delivery-note');

        Route::get('/invoice/{id}/print', 'SalesInvoiceController@print')->middleware('access.menu:sales/invoice');
        Route::get('/invoice/{id}/ajax-form', 'SalesInvoiceController@searchById')->middleware('access.menu:sales/invoice');
        Route::resource('/invoice', 'SalesInvoiceController')->names(["index" => "sales.invoice.index", "show" => "sales.invoice.show"])->middleware('access.menu:sales/invoice');
    });

    Route::group(['prefix' => 'production', 'namespace' => 'Production'], function () {
        Route::get('/bom/{id}/ajax-form', 'BillOfMaterialController@searchById')->middleware('access.menu:production/bom');
        Route::resource('/bom', 'BillOfMaterialController')->middleware('access.menu:production/bom');

        Route::get('/job-order/{id}/print', 'JobOrderController@print')->middleware('access.menu:production/job-order');
        Route::get('/job-order/{id}/ajax-form', 'JobOrderController@searchById')->middleware('access.menu:production/job-order');
        Route::resource('/job-order', 'JobOrderController')->middleware('access.menu:production/job-order');

        Route::get('/good-issued/{id}/print', 'GoodIssuedController@print')->middleware('access.menu:production/good-issued');
        Route::get('/good-issued/{id}/ajax-form', 'GoodIssuedController@searchById')->middleware('access.menu:production/good-issued');
        Route::resource('/good-issued', 'GoodIssuedController')->middleware('access.menu:production/good-issued');

        Route::get('/good-receipt/{id}/print', 'GoodReceiptController@print')->middleware('access.menu:production/good-receipt');
        Route::get('/good-receipt/{id}/ajax-form', 'GoodReceiptController@searchById')->middleware('access.menu:production/good-receipt');
        Route::resource('/good-receipt', 'GoodReceiptController')->middleware('access.menu:production/good-receipt');
    });

    Route::group(['prefix' => 'purchase', 'namespace' => 'Purchase'], function () {
        Route::get('/request/{id}/print', 'PurchaseRequestController@print')->middleware('access.menu:purchase/request');
        Route::get('/request/{id}/ajax-form', 'PurchaseRequestController@searchById')->middleware('access.menu:purchase/request');
        Route::resource('/request', 'PurchaseRequestController')->middleware('access.menu:purchase/request');

        Route::get('/order/{id}/print', 'PurchaseOrderController@print')->middleware('access.menu:purchase/order');
        Route::get('/order/{id}/ajax-form', 'PurchaseOrderController@searchById')->middleware('access.menu:purchase/order');
        Route::resource('/order', 'PurchaseOrderController')->names(["index" => "purchase.order.index", "show" => "purchase.order.show"])->middleware('access.menu:purchase/order');

        Route::get('/receipt/{id}/print', 'PurchaseReceiptController@print')->middleware('access.menu:purchase/receipt');
        Route::get('/receipt/{id}/ajax-form', 'PurchaseReceiptController@searchById')->middleware('access.menu:purchase/receipt');
        Route::resource('/receipt', 'PurchaseReceiptController')->names(["index" => "purchase.receipt.index", "show" => "purchase.receipt.show"])->middleware('access.menu:purchase/receipt');

        Route::get('/invoice/{id}/print', 'PurchaseInvoiceController@print')->middleware('access.menu:purchase/invoice');
        Route::get('/invoice/{id}/ajax-form/{format}', 'PurchaseInvoiceController@searchById')->middleware('access.menu:purchase/invoice');
        Route::resource('/invoice', 'PurchaseInvoiceController')->names(["index" => "purchase.invoice.index", "show" => "purchase.invoice.show"])->middleware('access.menu:purchase/invoice');
    });

    Route::group(['prefix' => 'inventory', 'namespace' => 'Inventory'], function () {
        Route::put('/balance/{id}/update-selling-price', 'InventoryBalanceController@storeSellingPrice');
        Route::get('/balance/{id}/update-selling-price', 'InventoryBalanceController@editSellingPrice');
        Route::resource('/balance', 'InventoryBalanceController');

        Route::resource('/movement', 'InventoryMovementController');
    });

    Route::group(['prefix' => 'asset', 'namespace' => 'Asset'], function () {
        Route::get('/loan/{id}/print', 'AssetLoanController@print')->middleware('access.menu:asset/loan');
        Route::get('/loan/{id}/ajax-form', 'AssetLoanController@searchById')->middleware('access.menu:asset/loan');
        Route::resource('/loan', 'AssetLoanController')->middleware('access.menu:asset/loan');

        Route::get('/return/{id}/print', 'AssetReturnController@print')->middleware('access.menu:asset/return');
        Route::get('/return/{id}/ajax-form', 'AssetReturnController@searchById')->middleware('access.menu:asset/return');
        Route::resource('/return', 'AssetReturnController')->middleware('access.menu:asset/return');

        Route::post('/stock/{id}', 'AssetStockController@update')->middleware('access.menu:asset/stock');
        Route::resource('/stock', 'AssetStockController')->middleware('access.menu:asset/stock');
    });

    Route::group(['prefix' => 'finance', 'namespace' => 'Finance'], function () {
        Route::resource('/account-receivable', 'AccountReceivableController');
        Route::resource('/account-payable', 'AccountPayableController');
        Route::post('/coa/ajax/getCOAAjax', 'COAController@getCOAAjax');
        Route::post('/coa/ajax/listCOA', 'COAController@listCOA');
        Route::post('/coa/import', 'COAController@import');
        Route::resource('/coa', 'COAController');
        Route::post('/voucher/ajaxVoucher', 'VoucherController@ajaxVoucher');
        Route::post('/voucher/voucherTable', 'VoucherController@voucherTable');
        Route::resource('/voucher', 'VoucherController');
        Route::resource('/journal/ajax/datatable', 'JournalController@datatable');
        Route::resource('/journal', 'JournalController');
        Route::resource('/journal', 'JournalController');
        Route::post('/income-statement/ajaxSelect', 'IncomeStatementController@ajaxSelect');
        Route::resource('/income-statement', 'IncomeStatementController');
        Route::resource("/balance-sheet", "BalanceSheetController");
        Route::group(["prefix" => "template", "namespace" => "Template", "as" => "finance.template."], function() {
          Route::post("income-statement/ajaxDataTable", "IncomeStatementController@ajaxDataTable");
          Route::resource("income-statement", "IncomeStatementController")->parameters(["" => "labarugi"]);

          Route::post("balance-sheet/ajaxDataTable", "BalanceSheetController@ajaxDataTable");
          Route::resource("balance-sheet", "BalanceSheetController")->parameters(["" => "balancesheet"]);
        });
    });

    Route::group(['prefix' => 'customer', 'namespace' => 'Customer'], function () {
        Route::resource('/application-paylater', 'ApplicationPaylaterController');
        Route::resource('/chat', 'ChatController');
        Route::resource('/review', 'ItemReviewController');
    });

    //chat
    Route::group(['prefix' => 'ajax/customer-chat', 'namespace' => 'Customer'], function () {
        Route::get('/type', 'ChatController@ajaxGetType');

        Route::get('/', 'ChatController@ajaxGetAll');
        Route::get('{chat_id}/message', 'ChatController@ajaxGetMessageByHeader');
        Route::post('{chat_id}/message', 'ChatController@ajaxSendMessageByHeader');

        // Route::delete('{chat_id}/message', 'ChatMessageController@destroy');
    });
});

// midtrans route
Route::get('payment/snaptest', 'APIMobile\OrderController@snaptest');
Route::get('payment/snaptoken/{snaptoken}', 'APIMobile\OrderController@snaptoken');
Route::get('payment/finish', 'APIMobile\OrderController@finish');
Route::get('payment/unfinish', 'APIMobile\OrderController@unfinish');
Route::get('payment/error', 'APIMobile\OrderController@error');
Route::post('payment/notification-handler', 'APIMobile\OrderController@notificationHandler');
