<?php

namespace App\Modules\Game\Models;

use App\Common\Utils\EnumArrayTrait;

enum GameWinner: int
{
    use EnumArrayTrait;
    
    case Catchers = 1;
    
    case Runners = 2;
}
