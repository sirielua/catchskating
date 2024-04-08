<?php

namespace App\Modules\Game\Requests\Game\Queries;

use App\MessageBus\QueryInterface;

class GetGame implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {}
}
