<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class CompleteGame
{
    public function __construct(
        public int $id,
        public bool $catchersWin,
        public ?int $duration = null, 
    ) {}
}
