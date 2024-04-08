<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class RulesHandler
{
    public function __invoke(Nutgram $bot)
    {
        Conversations\RulesMenu::begin($bot);
    }
}
