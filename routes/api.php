<?php

use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->name('api.')->group(function () {
    Route::get('/plots', [CatalogController::class, 'plots'])->name('plots.index');
    Route::get('/plots/{plot}', [CatalogController::class, 'plot'])->name('plots.show');
    Route::get('/installment-plans/{plan}', [CatalogController::class, 'plan'])->name('plans.show');
    Route::get('/locations', [CatalogController::class, 'locations'])->name('locations.index');
    Route::get('/faqs', [CatalogController::class, 'faqs'])->name('faqs.index');
    Route::get('/contact', [CatalogController::class, 'contact'])->name('contact');
});
