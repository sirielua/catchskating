<?php

namespace App\Modules\Game\Requests\Commands\Game;

readonly class ResumeGame
{
    public function __construct(
        public int $id,
    ) {}
}
