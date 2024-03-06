<?php

namespace App\Modules\Player\Models;

use App\Common\Utils\EnumArrayTrait;

enum Role: string
{
    use EnumArrayTrait;
    
    case Player = 'player';
    
    case Organizer = 'organizer';
    
    case Admin = 'admin';
}
