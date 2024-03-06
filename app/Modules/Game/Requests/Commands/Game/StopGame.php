<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class StopGame
{
    public function __construct(
        public int $id,
    ) {}
}
