<?php

namespace App\Modules\Game\Requests\Session\Queries;

use App\MessageBus\QueryInterface;

readonly class GetAvailableSessions implements QueryInterface
{
    public function __construct(
        public int $page = 1,
        public ?int $limit = null,
    ) {}
}
