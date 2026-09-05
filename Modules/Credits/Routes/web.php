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

Route::prefix('admin')->middleware(['auth', 'plan.feature:credits'])->group(function () {
    Route::get('credits/receivables', 'CreditController@receivables')->name('credits.receivables');
    Route::get('credits/payables', 'CreditController@payables')->name('credits.payables');
    Route::get('credits/kardex/customer/{id}', 'CreditController@customerKardex')->name('credits.kardex.customer');
    Route::get('credits/kardex/customer/{id}/pdf', 'CreditController@customerKardexPdf')->name('credits.kardex.customer.pdf');
    Route::get('credits/kardex/supplier/{id}', 'CreditController@supplierKardex')->name('credits.kardex.supplier');
    Route::get('credits/kardex/supplier/{id}/pdf', 'CreditController@supplierKardexPdf')->name('credits.kardex.supplier.pdf');
    Route::post('credits/abono', 'CreditController@storeAbono')->name('credits.abono.store');
    Route::post('credits/installment/pay', 'CreditController@payInstallment')->name('credits.installment.pay');
    Route::get('credits/receipt/{id}', 'CreditController@printReceipt')->name('credits.receipt.print');
});
