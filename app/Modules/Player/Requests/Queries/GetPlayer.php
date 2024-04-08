<?php

namespace App\Modules\Player\Requests\Queries;

use App\MessageBus\QueryInterface;

readonly class GetPlayer implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {}
}
