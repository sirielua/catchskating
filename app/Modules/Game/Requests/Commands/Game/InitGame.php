<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class InitGame
{
    public function __construct(
        public int $sessionId,
        public int $catchersCount,
        public int $runnersCount,
    ) {}
}
