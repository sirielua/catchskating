<?php

namespace App\Modules\Game\Requests\Session\Queries;

use App\MessageBus\QueryInterface;

class GetActualSession implements QueryInterface
{
    public function __construct(
        public int $playerId,
    ) {}
}
