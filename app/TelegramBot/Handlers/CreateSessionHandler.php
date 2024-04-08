<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class CreateSessionHandler
{
    public function __invoke(Nutgram $bot)
    {
        Conversations\CreateSessionConversation::begin($bot);
    }
}