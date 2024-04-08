<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class PlayerHandler
{
    public function __invoke(Nutgram $bot, ?int $id = null)
    {  
        Conversations\PlayerMenu::begin($bot, data: ['id' => $id]);
    }
}
