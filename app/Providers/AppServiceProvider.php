<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Force HTTPS on production / hosting environment to avoid mixed-content issues
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Share white-label branding variables globally across all Blade views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $appName = \App\Models\Setting::get('app_name', 'VoucherNet RT/RW');
                    $appSubtitle = \App\Models\Setting::get('app_subtitle', 'Sistem Voucher & Buku Kas');
                    $logoPath = \App\Models\Setting::get('app_logo_path');
                    $faviconPath = \App\Models\Setting::get('app_favicon_path');
                    $ownerName = \App\Models\User::where('role', 'super_admin')->value('name') ?? 'Owner';

                    $appLogoUrl = $logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
                        ? asset('storage/' . $logoPath)
                        : null;

                    $appFaviconUrl = $faviconPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($faviconPath)
                        ? asset('storage/' . $faviconPath)
                        : asset('favicon.svg');

                    $view->with([
                        'globalAppName' => $appName,
                        'globalAppSubtitle' => $appSubtitle,
                        'globalOwnerName' => $ownerName,
                        'globalAppLogo' => $appLogoUrl,
                        'globalAppFavicon' => $appFaviconUrl,
                    ]);
                    return;
                }
            } catch (\Throwable $e) {
                // Fallback gracefully during early boot or migrations
            }

            $view->with([
                'globalAppName' => 'VoucherNet RT/RW',
                'globalAppSubtitle' => 'Sistem Voucher & Buku Kas',
                'globalOwnerName' => 'Owner',
                'globalAppLogo' => null,
                'globalAppFavicon' => asset('favicon.svg'),
            ]);
        });
    }
}
