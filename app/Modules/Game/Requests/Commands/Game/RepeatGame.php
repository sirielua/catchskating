<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class RepeatGame
{
    public function __construct(
        public int $id,
    ) {}
}
