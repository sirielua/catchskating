<?php

namespace App\Modules\Game\Requests\Commands\Session;

readonly class ReopenSession
{
    public function __construct(
        public int $id,
    ) {}
}
