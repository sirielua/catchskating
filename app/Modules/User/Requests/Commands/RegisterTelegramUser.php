<?php

namespace App\Modules\User\Requests\Commands;

use App\MessageBus\CommandInterface;

readonly class RegisterTelegramUser implements CommandInterface
{
    public function __construct(
        public int $id,
        public ?string $username = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $name = null,
    ) {}
}
