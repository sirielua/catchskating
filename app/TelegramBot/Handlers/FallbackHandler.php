<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;

class FallbackHandler
{
    public function __invoke(Nutgram $bot)
    {
        $bot->asResponse()->sendMessage(sprintf(
            'Can\'t understand ya, %s. Your message was: "%s"',
            $bot->message()?->from?->first_name,
            $bot->message()?->text,
        ));
    }
}
