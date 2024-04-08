<?php

namespace App\Modules\Game\Requests\Game\Commands;

use App\MessageBus\CommandInterface;

readonly class CreateGame implements CommandInterface
{
    public function __construct(
        public int $sessionId,
        public int $catchersCount,
        public int $runnersCount,
    ) {}
}
