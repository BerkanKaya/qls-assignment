<?php

use App\Http\Controllers\ShippingLabelController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/shipping')->name('home');
Route::get('/shipping', [ShippingLabelController::class, 'create'])->name('shipping-label.create');
Route::post('/shipping', [ShippingLabelController::class, 'store'])->name('shipping-label.store');
