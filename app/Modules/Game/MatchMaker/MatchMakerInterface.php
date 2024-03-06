<?php

namespace App\Modules\Game\MatchMaker;

use App\Modules\Game\Models;
use Illuminate\Support\Collection;

interface MatchMakerInterface
{
    public function suggestPlayers(Models\Game $game): Collection;
}
