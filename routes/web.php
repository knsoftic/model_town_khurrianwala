<?php

use App\Http\Controllers\Front\BookingController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\InstallmentPlanController;
use App\Http\Controllers\Front\LeadController;
use App\Http\Controllers\Front\LocationController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\PlotController;
use App\Http\Controllers\Front\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::get('/plots', [PlotController::class, 'index'])->name('plots.index');
Route::get('/plots/{plot}', [PlotController::class, 'show'])->name('plots.show');
Route::get('/installment-plans', [InstallmentPlanController::class, 'index'])->name('installment-plans');

Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show');
Route::permanentRedirect('/location', '/locations');

Route::get('/development', [PageController::class, 'development'])->name('development');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/testimonials', [PageController::class, 'testimonials'])->name('testimonials');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/book-plot', [BookingController::class, 'create'])->name('book-plot');

Route::middleware('throttle:forms')->group(function () {
    Route::post('/inquiry', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/callback', [LeadController::class, 'callback'])->name('callback.store');
    Route::post('/book-plot', [BookingController::class, 'store'])->name('book-plot.store');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    Route::post('/newsletter', [ContactController::class, 'subscribe'])->name('newsletter.store');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots']);
