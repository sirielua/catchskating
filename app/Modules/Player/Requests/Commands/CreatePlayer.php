<?php

namespace App\Modules\Player\Requests\Commands;

use App\MessageBus\CommandInterface;

readonly class CreatePlayer implements CommandInterface
{
    public function __construct(
        public string $name,
    ) {}
}
