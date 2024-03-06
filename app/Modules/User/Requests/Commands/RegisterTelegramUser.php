<?php

namespace App\Modules\User\Requests\Commands;

readonly class RegisterTelegramUser
{
    public function __construct(
        public int $telegramId,
        public string $name,
    ) {}
}
