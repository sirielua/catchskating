<?php

namespace App\Modules\Game\Models;

use App\Common\Utils\EnumArrayTrait;

enum GameRole: int
{
    use EnumArrayTrait;
    
    case Catcher = 1;
    
    case Runner = 2;
}
