<?php

namespace App\Modules\Game\Requests\Session\Commands;

use App\MessageBus\CommandInterface;

readonly class CreateSession implements CommandInterface
{
    public function __construct(
        public \DateTimeImmutable $date,
        public ?string $description = null,
    ) {}
}
