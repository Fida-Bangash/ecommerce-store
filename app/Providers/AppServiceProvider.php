<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartOnLogin;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(Login::class, MergeGuestCartOnLogin::class);

        View::composer('partials.site-header', function ($view) {
            $view->with('cartItemCount', app(CartService::class)->currentItemCount());
        });
    }
}
