<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class RemovePlayers
{
    public function __construct(
        public int $id,
        public array $players = [],
    ) {}
}