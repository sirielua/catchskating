<?php

namespace App\Modules\Game\Requests\Session\Queries;

use App\MessageBus\QueryInterface;

class GetSession implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {}
}
