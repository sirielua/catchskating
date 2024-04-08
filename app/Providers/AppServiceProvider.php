<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private $providers = [
        \App\TelegramBot\Providers\TelegramBotProvider::class,
        \App\Modules\User\UserServiceProvider::class,
        \App\Modules\Player\PlayerServiceProvider::class,
        \App\Modules\Game\GameServiceProvider::class,
    ];
    
    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
