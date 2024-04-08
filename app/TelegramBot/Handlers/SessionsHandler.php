<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class SessionsHandler
{
    public function __invoke(Nutgram $bot)
    {
        Conversations\SessionsMenu::begin($bot);
    }
}
