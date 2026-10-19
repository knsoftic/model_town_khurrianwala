<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use App\Services\HtmlSanitizer;
use App\Services\InstallmentCalculator;
use App\Services\ResponsiveImages;
use App\Services\SettingsService;
use App\Services\ThemeService;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\FrontLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(ThemeService::class);
        $this->app->singleton(HtmlSanitizer::class);
        $this->app->singleton(InstallmentCalculator::class);
        $this->app->singleton(ResponsiveImages::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->mixedCase()->numbers()->uncompromised()
            : Password::min(8));

        if ($proxies = config('cms.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        $this->registerGates();
        $this->registerRateLimiters();

        View::composer('layouts.front', FrontLayoutComposer::class);
        View::composer('layouts.admin', AdminLayoutComposer::class);
    }

    protected function registerGates(): void
    {
        // hasPermission() already returns true for Super Admins, so no Gate::before is needed;
        // that keeps policy rules (e.g. "you cannot delete yourself") binding for everyone.
        foreach (array_keys(Permission::DEFINITIONS) as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        Gate::define('super-admin', fn (User $user) => $user->isSuperAdmin());
    }

    protected function registerRateLimiters(): void
    {
        RateLimiter::for('forms', fn (Request $request) => [
            Limit::perMinute(5)->by('forms-min:'.$request->ip())->response(fn () => $this->tooManyAttempts($request)),
            Limit::perDay(50)->by('forms-day:'.$request->ip())->response(fn () => $this->tooManyAttempts($request)),
        ]);

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
    }

    protected function tooManyAttempts(Request $request): mixed
    {
        $message = 'Too many submissions. Please wait a minute and try again, or call us directly.';

        return $request->expectsJson()
            ? response()->json(['message' => $message], 429)
            : back()->withInput()->withErrors(['form' => $message]);
    }
}
