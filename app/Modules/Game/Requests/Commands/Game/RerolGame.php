<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class RerolGame
{
    public function __construct(
        public int $id,
        public int $catchersCount,
        public int $runnersCount,
    ) {}
}
