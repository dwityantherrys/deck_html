<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*api backend*/
Route::group(['prefix' => 'backend'], function () {
  Route::post('shipping/cost', 'APIMobile\ShippingCostController@index');

  Route::get('/voucher/{id}', 'Mobile\Voucher\VoucherController@searchById');
  Route::get('/voucher', 'Mobile\Voucher\VoucherController@search');

  Route::get('/province/{id}', 'Master\City\ProvinceController@searchById');
  Route::get('/province', 'Master\City\ProvinceController@search');

  Route::get('/city/{id}', 'Master\City\CityController@searchById');
  Route::get('/city', 'Master\City\CityController@search');

  Route::get('/city/{city_id}/district/{id}', 'Master\City\DistrictController@searchById');
  Route::get('/city/{city_id}/district', 'Master\City\DistrictController@search');
  
  Route::get('/production/good-receipt/{id}', 'Production\GoodReceiptController@searchById');
  Route::get('/production/good-receipt', 'Production\GoodReceiptController@search');

  Route::get('/production/good-issued/{id}', 'Production\GoodIssuedController@searchById');
  Route::get('/production/good-issued', 'Production\GoodIssuedController@search');

  Route::get('/production/job-order/issued/{id}', 'Production\JobOrderController@searchJobIssuedById');
  Route::get('/production/job-order/{id}', 'Production\JobOrderController@searchById');
  Route::get('/production/job-order', 'Production\JobOrderController@search');

  Route::get('/production/bom/item-material/{id}', 'Production\BillOfMaterialController@searchByItemMaterialId');
  Route::get('/production/bom/{id}', 'Production\BillOfMaterialController@searchById');
  Route::get('/production/bom', 'Production\BillOfMaterialController@search');

  Route::get('/sales/delivery/{id}', 'Sales\DeliveryNoteController@searchById');
  Route::get('/sales/delivery', 'Sales\DeliveryNoteController@search');

  Route::get('/sales/instruction/{id}', 'Sales\ShippingInstructionController@searchById');
  Route::get('/sales/instruction', 'Sales\ShippingInstructionController@search');

  // Route::get('/sales/order/{id}/{format}', 'Sales\SalesOrderController@searchById');
  Route::get('/sales/order/shipping/{id}', 'Sales\SalesOrderController@searchShippingFormatById');
  Route::get('/sales/order/{id}', 'Sales\SalesOrderController@searchById');
  Route::get('/sales/order', 'Sales\SalesOrderController@search');

  Route::get('/sales/quotation/{id}', 'Sales\SalesQuotationController@searchById');
  Route::get('/sales/quotation', 'Sales\SalesQuotationController@search');

  Route::get('/purchase/receipt/{id}/{format}', 'Purchase\PurchaseReceiptController@searchById');
  Route::get('/purchase/receipt/{id}', 'Purchase\PurchaseReceiptController@searchById');
  Route::get('/purchase/receipt', 'Purchase\PurchaseReceiptController@search');

  Route::get('/purchase/order/{id}', 'Purchase\PurchaseOrderController@searchById');
  Route::get('/purchase/order', 'Purchase\PurchaseOrderController@search');

  Route::get('/purchase/request/{id}', 'Purchase\PurchaseRequestController@searchById');
  Route::get('/purchase/request', 'Purchase\PurchaseRequestController@search');

  Route::get('/purchase/requestship/{id}', 'Purchase\PurchaseRequestController@searchByIdShip');
  Route::get('/purchase/requestship', 'Purchase\PurchaseRequestController@searchShip');

  Route::get('/item-material/{id}/warehouse/{warehouse_id}', 'Master\Item\ItemMaterialController@searchInWarehouseById');
  Route::get('/item-material/{id}', 'Master\Item\ItemMaterialController@searchById');
  Route::get('/item-material', 'Master\Item\ItemMaterialController@search');

  Route::get('/item-category/{id}', 'Master\Item\ItemCategoryController@searchById');
  Route::get('/item-category', 'Master\Item\ItemCategoryController@search');
  
  Route::get('/items/{id}', 'Master\Item\ItemController@searchById');
  Route::get('/items', 'Master\Item\ItemController@search');

  // Route::get('/items-service/{id}', 'Master\Item\ItemController@searchByIdService');
  // Route::get('/items-service', 'Master\Item\ItemController@searchService');

  Route::get('/items-service/{id?}', 'Master\Item\ItemController@searchService');

  Route::get('/items-sparepart/{id}', 'Master\Item\ItemController@searchByIdSparepart');
  Route::get('/items-sparepart', 'Master\Item\ItemController@searchSparepart');

  Route::get('/inventory/{warehouse_id}/{inventory_type}/{id}', 'Inventory\InventoryController@searchById');
  Route::get('/inventory/{warehouse_id}/{inventory_type}', 'Inventory\InventoryController@search');

  Route::get('/inventory-warehouse/{warehouse_id}/{raw_material_id}/{id}', 'Inventory\InventoryWarehouseController@searchById');
  Route::get('/inventory-warehouse/{warehouse_id}/{raw_material_id}', 'Inventory\InventoryWarehouseController@search');

  Route::get('/asset/{id}', 'Inventory\Asset\AssetController@searchById');
  Route::get('/asset', 'Inventory\Asset\AssetController@search');

  Route::get('/asset-stock/{id}/warehouse/{warehouse_id}', 'Inventory\Asset\AssetStockController@searchInWarehouseById');
  Route::get('/asset-stock/{id}', 'Inventory\Asset\AssetStockController@searchById');
  Route::get('/asset-stock', 'Inventory\Asset\AssetStockController@search');

  Route::get('/asset-category/{id}', 'Inventory\Asset\AssetCategoryController@searchById');
  Route::get('/asset-category', 'Inventory\Asset\AssetCategoryController@search');

  Route::get('/brand/{id}', 'Master\BrandController@searchById');
  Route::get('/brand', 'Master\BrandController@search');

  Route::get('/raw-material/{id}', 'Master\Material\RawMaterialController@searchById');
  Route::get('/raw-material', 'Master\Material\RawMaterialController@search');

  Route::get('/material/{id}', 'Master\Material\MaterialController@searchById');
  Route::get('/material', 'Master\Material\MaterialController@search');

  Route::get('/color/{id}', 'Master\Material\ColorController@searchById');
  Route::get('/color', 'Master\Material\ColorController@search');

  Route::get('/company/{id}', 'Master\Customer\CompanyController@searchById');
  Route::get('/company', 'Master\Customer\CompanyController@search');

  // Route::get('/sales/{id}', 'Sales\SalesQuotationController@searchById');
  // Route::get('/sales', 'Sales\SalesQuotationController@search');

  Route::get('/warehouse/{id}', 'Master\WarehouseController@searchById');
  Route::get('/warehouse', 'Master\WarehouseController@search');
  
  Route::get('/factory/{id}', 'Master\WarehouseController@searchById');
  Route::get('/factory', 'Master\WarehouseController@searchFactory');

  Route::get('/customer/{id}/address', 'Master\Customer\CustomerController@searchAddressByUserId');
  Route::get('/customer/{id}', 'Master\Customer\CustomerController@searchById');
  Route::get('/customer', 'Master\Customer\CustomerController@search');

  Route::get('/employee/{id}', 'Master\Employee\EmployeeController@searchById');
  Route::get('/employee', 'Master\Employee\EmployeeController@search');

  Route::get('/branch/{id}', 'Master\BranchController@searchById');
  Route::get('/branch', 'Master\BranchController@search');

  Route::get('/unit/{id}', 'Master\UnitController@searchById');
  Route::get('/unit', 'Master\UnitController@search');
});
/*end api backend*/ 

/**
 * Mobile API
 */
Route::group(['namespace' => 'APIMobile'], function () {
  Route::post('customer/sign-in', 'CustomerController@signIn');
  Route::post('customer/forgot-password', 'ForgetPasswordController@forgotPassword');

  Route::apiResource('province', 'ProvinceController');
  Route::get('province/{id}/city', 'CityController@index');
  
  Route::apiResource('city', 'CityController');
  Route::get('city/{id}/district', 'DistrictController@index');
  Route::get('city/{id}/warehouse', 'WarehouseController@index');

  Route::apiResource('district', 'DistrictController');

  Route::apiResource('warehouse', 'WarehouseController');

  Route::get('banner', 'BannerMobileController@index');

  Route::group(['middleware' => 'isAuthanticate'], function(){
    Route::get('customer', 'CustomerController@show');
    Route::put('customer', 'CustomerController@update');
    Route::post('customer/update-thumbnail', 'CustomerController@updateThumbnail');
    Route::post('customer/update-ktp-image', 'CustomerController@updateIdentityImage');
    Route::post('customer/sign-out', 'CustomerController@signOut');
    
    Route::get('customer/{id}/address', 'CustomerAddressController@index');
    Route::get('customer/{id}/address/{address_id}', 'CustomerAddressController@show');
    Route::post('customer/{id}/address', 'CustomerAddressController@store');
    Route::put('customer/{id}/address/{address_id}', 'CustomerAddressController@update');
    Route::delete('customer/{id}/address/{address_id}', 'CustomerAddressController@destroy');

    Route::get('customer/{id}/cart', 'CartController@index');
    Route::post('customer/{id}/cart', 'CartController@store');
    Route::get('customer/{id}/cart/{cart_id}', 'CartController@show');
    Route::put('customer/{id}/cart', 'CartController@update');
    Route::put('customer/{id}/cart/{cart_id}', 'CartController@store');
    Route::delete('customer/{id}/cart/{cart_id}', 'CartController@destroy');

    Route::get('customer/{id}/order', 'OrderController@index');
    Route::get('customer/{id}/order-history', 'OrderController@history');
    Route::get('customer/{id}/order-history-with-paylater', 'OrderController@historyPaylater');
    Route::get('customer/{id}/order-history/{sales_id}', 'OrderController@historyById');
    Route::get('customer/{id}/order-invoice/{sales_id}', 'OrderController@invoiceById');
    Route::post('customer/{id}/order', 'OrderController@store');
    Route::get('customer/{id}/order/{order_id}/rest-bill', 'OrderController@checkoutRestBill');
    Route::post('customer/{id}/order/{order_id}/rest-bill', 'OrderController@storeCheckoutRestBill');

    Route::get('customer/{id}/application-paylater', 'ApplicationPaylaterController@index');
    Route::post('customer/{id}/application-paylater', 'ApplicationPaylaterController@store');

    Route::post('redeem-voucher', 'VoucherUsageController@store');

    Route::post('shipping/cost', 'ShippingCostController@index');

    Route::get('category/{id}/items', 'ItemController@index');
    Route::apiResource('item', 'ItemController');

    Route::post('item/review', 'ItemReviewController@store');
    Route::get('item/{item_id}/review', 'ItemReviewController@index');

    Route::get('customer/chat/type', 'ChatController@type');
    Route::get('customer/{customer_id}/chat', 'ChatController@index');
    Route::post('customer/{customer_id}/chat', 'ChatController@store');
    Route::get('customer/{customer_id}/chat/{chat_id}', 'ChatMessageController@index');
    Route::post('customer/{customer_id}/chat/{chat_id}', 'ChatMessageController@store');
    // Route::delete('customer/{customer_id}/chat/{chat_id}', 'ChatMessageController@destroy');
  });
  
  Route::post('customer', 'CustomerController@store');
  
  Route::apiResource('category', 'ItemCategoryController');

  // Route::get('customer/order', 'OrderController@redirectTest');

});
