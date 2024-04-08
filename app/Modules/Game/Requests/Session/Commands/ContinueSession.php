<?php

namespace App\Modules\Game\Requests\Session\Commands;

use App\MessageBus\CommandInterface;

readonly class ContinueSession implements CommandInterface
{
    public function __construct(
        public int $id,
    ) {}
}
