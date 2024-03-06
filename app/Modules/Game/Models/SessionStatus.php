<?php

namespace App\Modules\Game\Models;

use App\Common\Utils\EnumArrayTrait;

enum SessionStatus: string
{
    use EnumArrayTrait;
    
    case Pending = 'pending';
    
    case Active = 'active';
    
    case Closed = 'closed';
}
