<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class StartHandler
{
    public function __invoke(Nutgram $bot)
    {
        Conversations\StartMenu::begin($bot);
    }
}
