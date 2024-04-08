<?php

namespace App\Modules\Game\Requests\Game\Commands;

use App\MessageBus\CommandInterface;

readonly class ResumeGame implements CommandInterface
{
    public function __construct(
        public int $id,
    ) {}
}
