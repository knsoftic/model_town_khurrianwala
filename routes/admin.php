<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\BookingRequestController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DevelopmentMilestoneController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\InstallmentPlanController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadFieldController;
use App\Http\Controllers\Admin\LeadFollowUpController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PlotAvailabilityController;
use App\Http\Controllers\Admin\PlotController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectFeatureController;
use App\Http\Controllers\Admin\ReorderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoGuideController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes — prefix "/{ADMIN_PATH}", names "admin.*" (see bootstrap/app.php)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');

    // Generic drag-and-drop ordering and on/off switches (permission checked per resource).
    Route::post('reorder/{resource}', [ReorderController::class, 'reorder'])->name('reorder');
    Route::patch('toggle/{resource}/{id}', [ReorderController::class, 'toggle'])->name('toggle');

    // Plots
    Route::middleware('can:plots')->group(function () {
        Route::get('plots/availability', [PlotAvailabilityController::class, 'index'])->name('plots.availability');
        Route::put('plots/availability', [PlotAvailabilityController::class, 'update'])->name('plots.availability.update');
        Route::get('plots/featured', [PlotAvailabilityController::class, 'featured'])->name('plots.featured');
        Route::post('plots/{plot}/duplicate', [PlotController::class, 'duplicate'])->name('plots.duplicate');
        Route::delete('plots/{plot}/images/{image}', [PlotController::class, 'destroyImage'])->name('plots.images.destroy');
        Route::resource('plots', PlotController::class)->except('show');
    });

    // Installment plans & payment schedules
    Route::middleware('can:installments')->group(function () {
        Route::get('installment-plans/schedules', [InstallmentPlanController::class, 'schedules'])->name('installment-plans.schedules');
        Route::post('installment-plans/{installment_plan}/duplicate', [InstallmentPlanController::class, 'duplicate'])->name('installment-plans.duplicate');
        Route::post('installment-plans/{installment_plan}/apply-to-plot', [InstallmentPlanController::class, 'applyToPlot'])->name('installment-plans.apply');
        Route::resource('installment-plans', InstallmentPlanController::class)->except('show');
    });

    // Leads: staff with "leads" see every lead, agents ("my_leads") only their own (checked per lead).
    Route::middleware('can:leads.access')->group(function () {
        Route::get('leads/export/{format}', [LeadController::class, 'export'])->whereIn('format', ['csv', 'xlsx', 'docx'])->name('leads.export');
        Route::patch('leads/{lead}/quick', [LeadController::class, 'quickUpdate'])->name('leads.quick');
        Route::post('leads/{lead}/notes', [NoteController::class, 'storeForLead'])->name('leads.notes.store');
        Route::post('leads/{lead}/follow-ups', [LeadFollowUpController::class, 'store'])->name('leads.follow-ups.store');
        Route::patch('follow-ups/{followUp}', [LeadFollowUpController::class, 'update'])->name('follow-ups.update');
        Route::delete('follow-ups/{followUp}', [LeadFollowUpController::class, 'destroy'])->name('follow-ups.destroy');
        Route::resource('leads', LeadController::class)->except('edit');
    });

    // Lead form fields, bulk changes, contact messages, newsletter
    Route::middleware('can:leads')->group(function () {
        Route::post('leads/bulk', [LeadController::class, 'bulk'])->name('leads.bulk');
        Route::resource('lead-fields', LeadFieldController::class)->except('show');

        Route::post('messages/{message}/convert', [ContactMessageController::class, 'convert'])->name('messages.convert');
        Route::resource('messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);

        Route::get('subscribers/export', [SubscriberController::class, 'export'])->name('subscribers.export');
        Route::resource('subscribers', SubscriberController::class)->only(['index', 'destroy']);
    });

    // Booking requests
    Route::middleware('can:bookings')->group(function () {
        Route::get('bookings/export/{format}', [BookingRequestController::class, 'export'])->whereIn('format', ['csv', 'xlsx'])->name('bookings.export');
        Route::post('bookings/{booking}/notes', [NoteController::class, 'storeForBooking'])->name('bookings.notes.store');
        Route::resource('bookings', BookingRequestController::class)->only(['index', 'show', 'update', 'destroy']);
    });

    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::resource('testimonials', TestimonialController::class)->except('show')->middleware('can:testimonials');
    Route::resource('locations', LocationController::class)->except('show')->middleware('can:locations');

    // Gallery & videos
    Route::middleware('can:gallery')->group(function () {
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery/albums', [GalleryController::class, 'storeAlbum'])->name('gallery.albums.store');
        Route::get('gallery/albums/{album}', [GalleryController::class, 'show'])->name('gallery.albums.show');
        Route::put('gallery/albums/{album}', [GalleryController::class, 'updateAlbum'])->name('gallery.albums.update');
        Route::delete('gallery/albums/{album}', [GalleryController::class, 'destroyAlbum'])->name('gallery.albums.destroy');
        Route::post('gallery/albums/{album}/images', [GalleryController::class, 'storeImages'])->name('gallery.images.store');
        Route::put('gallery/images/{image}', [GalleryController::class, 'updateImage'])->name('gallery.images.update');
        Route::delete('gallery/images/{image}', [GalleryController::class, 'destroyImage'])->name('gallery.images.destroy');
        Route::resource('videos', VideoController::class)->except('show');
    });

    // Website content (CMS)
    Route::middleware('can:cms')->group(function () {
        Route::get('sections/create', [HomepageSectionController::class, 'create'])->name('sections.create');
        Route::resource('sections', HomepageSectionController::class)->except('show');
        Route::resource('pages', PageController::class)->only(['index', 'edit', 'update']);
        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        Route::put('menus/{item}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{item}', [MenuController::class, 'destroy'])->name('menus.destroy');
        Route::resource('features', ProjectFeatureController::class)->except('show');
        Route::resource('development', DevelopmentMilestoneController::class)->except('show')->parameters(['development' => 'milestone']);
        Route::resource('statistics', StatisticController::class)->except('show');
        Route::resource('faqs', FaqController::class)->except('show');
    });

    // Settings
    Route::middleware('can:settings')->group(function () {
        Route::get('settings/{group}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/notifications/test-email', [SettingController::class, 'testEmail'])->name('settings.test-email');
        Route::get('seo-guide', [SeoGuideController::class, 'index'])->name('seo-guide');
        Route::post('seo-guide/link', [SeoGuideController::class, 'resetLink'])->name('seo-guide.link');
        Route::resource('social-links', SocialLinkController::class)->except(['show', 'create', 'edit']);
    });

    // Users, roles, activity
    Route::middleware('can:users')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
    });

    // Reports
    Route::middleware('can:reports')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('reports/{report}/export/{format}', [ReportController::class, 'export'])->whereIn('format', ['csv', 'xlsx', 'pdf'])->name('reports.export');
    });
});
