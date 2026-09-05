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

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('products/expiring', 'ProductController@expiringProducts')->name('products.expiring');
    Route::get('products/stock-zero', 'ProductController@zeroStock')->name('products.zero');
    Route::get('products/stock-zero/excel', 'ProductController@zeroStockExcel')->name('products.zero.excel');
    Route::get('products/{product}/barcode', 'ProductController@printBarcode')->name('products.barcode');
    Route::resource('products', 'ProductController');
    Route::resource('categories', 'CategoryController');

    // Marcas
    Route::resource('brands', 'BrandController');
    Route::post('brands/status/{id}', 'BrandController@changeStatus')->name('brands.status');
});
