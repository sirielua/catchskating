<?php

namespace App\Modules\Game\Models;

use App\Common\Utils\EnumArrayTrait;

enum PlayerCondition: string
{
    use EnumArrayTrait;
    
    case Ready = 'ready';
    
    case Catching = 'catching';
    
    case Running = 'running';
    
    case Resting = 'resting';
}
