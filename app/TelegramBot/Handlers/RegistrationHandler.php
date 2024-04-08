<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class RegistrationHandler
{
    public function __invoke(Nutgram $bot)
    {
        Conversations\RegistrationMenu::begin($bot);
    }
}
