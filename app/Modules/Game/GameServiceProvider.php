<?php

namespace App\Modules\Game;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\MessageBus\MessageBusAwareProvider;
use App\Modules\Game\Requests;
use App\Modules\Game\MatchMaker;

class GameServiceProvider extends ServiceProvider
{
    use MessageBusAwareProvider;
    
    private $commandsMapping = [
        Requests\Session\Commands\CreateSession::class => SessionService::class.'@'.'create',
        Requests\Session\Commands\UpdateSession::class => SessionService::class.'@'.'update',
        Requests\Session\Commands\AddPlayers::class => SessionService::class.'@'.'addPlayers',
        Requests\Session\Commands\RemovePlayers::class => SessionService::class.'@'.'removePlayers',
        Requests\Session\Commands\UpdatePlayerCondition::class => SessionService::class.'@'.'updatePlayerCondition',
        Requests\Session\Commands\StartSession::class => SessionService::class.'@'.'start',
        Requests\Session\Commands\EndSession::class => SessionService::class.'@'.'end',
        Requests\Session\Commands\ContinueSession::class => SessionService::class.'@'.'continue',
        
        Requests\Game\Commands\CreateGame::class => GameService::class.'@'.'create',
        Requests\Game\Commands\RerollGame::class => GameService::class.'@'.'reroll',
        Requests\Game\Commands\StartGame::class => GameService::class.'@'.'start',
        Requests\Game\Commands\StopGame::class => GameService::class.'@'.'stop',
        Requests\Game\Commands\ResumeGame::class => GameService::class.'@'.'resume',
        Requests\Game\Commands\CompleteGame::class => GameService::class.'@'.'complete',
        Requests\Game\Commands\AbortGame::class => GameService::class.'@'.'abort',
        Requests\Game\Commands\RepeatGame::class => GameService::class.'@'.'repeat',
    ];
    
    private $queriesMapping = [
        Requests\Session\Queries\GetSession::class => SessionService::class.'@'.'get',
        Requests\Session\Queries\GetActualSession::class => SessionService::class.'@'.'getActual',
        Requests\Session\Queries\GetAvailableSessions::class => SessionService::class.'@'.'getAvailable',
        
        Requests\Game\Queries\GetGame::class => GameService::class.'@'.'get',
    ];
    
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Events\GameCompleted::class => [
            Listeners\UpdateSessionPlayers::class,
        ],
    ];
    
    public $singletons = [
        MatchMaker\MatchMakerInterface::class => MatchMaker\DefaultMatchMaker::class,
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
