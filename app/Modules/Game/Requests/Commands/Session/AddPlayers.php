<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class AddPlayers
{
    public function __construct(
        public int $id,
        public array $players = [],
    ) {}
}