<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::resource('{module}', '{Module}Controller');
});
