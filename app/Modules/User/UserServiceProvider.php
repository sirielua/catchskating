<?php

namespace App\Modules\User;

use Illuminate\Support\ServiceProvider;
use App\MessageBus\MessageBusAwareProvider;
use App\Modules\User\Requests\Commands;
use App\Modules\User\Requests\Queries;

class UserServiceProvider extends ServiceProvider
{
    use MessageBusAwareProvider;
    
    private $commandsMapping = [
        Commands\RegisterTelegramUser::class => RegistrationService::class.'@'.'registerTelegramUser',
    ];
    
    private $queriesMapping = [
        Queries\GetUserByTelegram::class => UserService::class.'@'.'getByTelegram',
    ];
    
    public $singletons = [];
        
    #[\Override]
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        $this->bootMessageBuses();
    }
}
