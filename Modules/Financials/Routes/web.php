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
    Route::get('cajas/history', 'CashierController@history')->name('financials.cajas.history');
    Route::resource('cajas', 'CashierController')->names('financials.cajas');
    Route::post('cajas/{id}/close', 'CashierController@close')->name('financials.cajas.close');
    Route::get('cajas/{id}/arqueo', 'CashierController@arqueo')->name('financials.cajas.arqueo');
    Route::resource('expenses', 'ExpenseController')->names('financials.expenses');
    Route::get('insumos/consumo', 'InsumoConsumptionController@index')->name('financials.insumos.consume');
    Route::post('insumos/consumo', 'InsumoConsumptionController@store')->name('financials.insumos.consume.store');
    Route::delete('insumos/consumo/{id}', 'InsumoConsumptionController@destroy')->name('financials.insumos.consume.destroy');
});
