<?php

namespace App\Modules\User\Requests\Queries;

use App\MessageBus\QueryInterface;

readonly class GetUserByTelegram implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {}
}
