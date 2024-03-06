<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class OpenSession
{
    public function __construct(
        public int $id,
    ) {}
}
