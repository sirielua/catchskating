<?php

namespace App\Modules\Game\Requests\Session\Commands;

use App\MessageBus\CommandInterface;

readonly class UpdateSession implements CommandInterface
{
    public function __construct(
        public int $id,
        public ?\DateTimeImmutable $date = null,
        public ?string $description = null,
        public ?string $status = null,
    ) {}
}
