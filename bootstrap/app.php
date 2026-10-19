<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\NoIndex;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then: function () {
            Route::middleware(['web', 'noindex'])
                ->prefix(config('cms.admin_path'))
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminAccess::class,
            'noindex' => NoIndex::class,
        ]);

        $middleware->web(append: [SecurityHeaders::class]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
