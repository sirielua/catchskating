<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\MessageBus\MessageBus;
use App\MessageBus\TransactionBus;
use App\MessageBus\QueueMessageBus;
use App\MessageBus\ApplicationMessageManager;

class MessageBusServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        // Commands are meant to modify the application state.
        $this->app->singleton('CommandBus', function ($app) {
            return new TransactionBus(new MessageBus($app));
        });
        
        // Queries are meant to query application state
        $this->app->singleton('QueryBus', function ($app) {
            return new MessageBus($app);
        });
        
        $this->app->singleton('QueueBus', QueueMessageBus::class);
        
        $this->app->singleton(ApplicationMessageManager::class, function ($app) {
            return new ApplicationMessageManager(
                commandBus: $app->make('CommandBus'),
                queryBus: $app->make('QueryBus'),
                queueBus: $app->make('QueueBus'),
            );
        });
    }
    
    public function boot()
    {

    }
}


