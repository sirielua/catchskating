<?php

namespace App\Modules\Game\Requests\Session\Commands;

use App\MessageBus\CommandInterface;

readonly class UpdatePlayerCondition implements CommandInterface
{
    public function __construct(
        public int $sessionId,
        public int $playerId,
        public string $condition,
    ) {}
}