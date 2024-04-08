<?php

namespace App\Modules\Player;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\MessageBus\MessageBusAwareProvider;
use App\Modules\Player\Requests\Commands;
use App\Modules\Player\Requests\Queries;
use App\Modules\Game\Events as GameEvents;

class PlayerServiceProvider extends ServiceProvider
{
    use MessageBusAwareProvider;
    
    private $commandsMapping = [
        Commands\CreatePlayer::class => PlayerService::class.'@'.'create',
    ];
    
    private $queriesMapping = [
        Queries\GetPlayer::class => PlayerService::class.'@'.'get',
    ];
    
    public $singletons = [];
    
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        GameEvents\GameCompleted::class => [
            Listeners\UpdatePlayers::class,
        ],
    ];
    
    #[\Override]
    public function register(): void
    {
        parent::register();
    }
    
    #[\Override]
    public function boot(): void
    {
        $this->bootMessageBuses();
    }
}
