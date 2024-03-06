<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class UpdateSession
{
    public function __construct(
        public int $id,
        public ?string $name = null,
        public ?\DateTimeImmutable $date = null,
        public ?string $status = null,
    ) {}
}
