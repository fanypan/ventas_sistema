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

Route::prefix('admin')->middleware(['auth'])->group(function() {
    Route::resource('sales', 'SaleController');
    Route::get('pos', 'SaleController@pos')->name('sales.pos');
    Route::get('sales/{id}/print-ticket', 'SaleController@printTicket')->name('sales.print_ticket');
    Route::get('sales/{id}/print-invoice', 'SaleController@printInvoice')->name('sales.print_invoice');
    Route::post('sales/{id}/void', 'SaleController@void')->name('sales.void');

    // AJAX Routes
    Route::post('sales/ajax/search-product', 'SalesAjaxController@searchProduct')->name('sales.ajax.search_product');
    Route::post('sales/ajax/add-to-cart', 'SalesAjaxController@addToCart')->name('sales.ajax.add_to_cart');
    Route::get('sales/ajax/get-cart', 'SalesAjaxController@getCart')->name('sales.ajax.get_cart');
    Route::post('sales/ajax/remove-from-cart', 'SalesAjaxController@removeFromCart')->name('sales.ajax.remove_from_cart');
    Route::post('sales/ajax/search-customer', 'SalesAjaxController@searchCustomer')->name('sales.ajax.search_customer');
    Route::get('sales/ajax/list-customers', 'SalesAjaxController@listCustomers')->name('sales.ajax.list_customers');
    Route::post('sales/ajax/store-customer', 'SalesAjaxController@storeCustomer')->name('sales.ajax.store_customer');
    Route::post('sales/ajax/update-cart-item', 'SalesAjaxController@updateCartItem')->name('sales.ajax.update_cart_item');
    Route::post('sales/ajax/process-sale', 'SalesAjaxController@processSale')->name('sales.ajax.process_sale');
    Route::post('sales/ajax/clear-cart', 'SalesAjaxController@clearCart')->name('sales.ajax.clear_cart');
});
