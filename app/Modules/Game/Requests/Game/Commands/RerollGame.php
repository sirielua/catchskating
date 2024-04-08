<?php

namespace App\Modules\Game\Requests\Game\Commands;

use App\MessageBus\CommandInterface;

readonly class RerollGame implements CommandInterface
{
    public function __construct(
        public int $id,
    ) {}
}
