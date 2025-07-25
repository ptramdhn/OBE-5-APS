<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (str_contains(request()->url(), 'ngrok-free.app')) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);
        JsonResource::withoutWrapping();
    }
}
