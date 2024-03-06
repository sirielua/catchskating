<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class CreateSession
{
    public function __construct(
        public string $name,
        public \DateTimeImmutable $date,
    ) {}
}
