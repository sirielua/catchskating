<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class CloseSession
{
    public function __construct(
        public int $id,
    ) {}
}
