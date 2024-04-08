<?php

namespace App\Modules\Game\Requests\Game\Commands;

use App\MessageBus\CommandInterface;
use App\Modules\Game\Models\GameWinner;

readonly class CompleteGame implements CommandInterface
{
    public function __construct(
        public int $id,
        public GameWinner $winner,
        public ?int $duration = null, 
    ) {}
}
