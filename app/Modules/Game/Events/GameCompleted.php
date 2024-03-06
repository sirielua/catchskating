<?php

namespace App\Modules\Game\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Game\Models\Game;

class GameCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(
        public Game $game,
    ) {}
}
