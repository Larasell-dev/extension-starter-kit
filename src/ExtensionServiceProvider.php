<?php

namespace Larasell\Extension;

use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/larasell/extension.php', 'larasell.extension');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Admin pages: register tables and navigation here, e.g.
        // Navigation::index('Reviews', 'product-reviews', ProductReviews::class, icon: Icon::Star)->under(NavItem::Products);
        // Admin routes go in AdminPanel::resolve()->routes(fn () => ...), so they get the panel's middleware.

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/larasell/extension.php' => config_path('larasell/extension.php'),
            ], 'larasell-extension-config');
        }
    }
}
