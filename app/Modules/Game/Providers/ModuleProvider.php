<?php

namespace App\Modules\Game\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Game\MatchMaker;

class ModuleProvider extends ServiceProvider
{
    public $singletons = [
        MatchMaker\MatchMakerInterface::class => MatchMaker\DefaultMatchMaker::class,
    ];
        
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        //
    }
}
