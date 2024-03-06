<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class StartGame
{
    public function __construct(
        public int $id,
    ) {}
}
