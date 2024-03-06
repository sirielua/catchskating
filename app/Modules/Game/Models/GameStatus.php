<?php

namespace App\Modules\Game\Models;

use App\Common\Utils\EnumArrayTrait;

enum GameStatus: string
{
    use EnumArrayTrait;
    
    case Draft = 'draft';
    
    case Ongoing = 'ongoing';
    
    case Stopped = 'stopped';
    
    case Completed = 'completed';
    
    case Aborted = 'aborted';
}
