<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class SessionHandler
{
    public function __invoke(Nutgram $bot, ?int $id = null)
    {
        Conversations\SessionMenu::begin($bot, data: ['sessionId' => $id]);
    }
}
