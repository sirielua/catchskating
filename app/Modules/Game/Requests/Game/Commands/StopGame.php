<?php

namespace App\Modules\Game\Requests\Game\Commands;

use App\MessageBus\CommandInterface;

readonly class StopGame implements CommandInterface
{
    public function __construct(
        public int $id,
    ) {}
}
