<?php

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::prefix('stock-adjustments')->group(function () {
        Route::get('/', 'InventoryAdjustmentController@index')->name('stock.adjustments.index');
        Route::post('/', 'InventoryAdjustmentController@store')->name('stock.adjustments.store');
    });
});
