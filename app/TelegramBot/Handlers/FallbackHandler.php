<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\App;

class FallbackHandler
{
    public function __invoke(Nutgram $bot)
    {
        if (App::hasDebugModeEnabled()) {
            $bot->asResponse()->sendMessage(sprintf(
                'Can\'t understand ya, %s. Your message was: "%s"',
                $bot->message()?->from?->first_name,
                $bot->message()?->text,
            ));
        } else {
            $bot->sendMessage('Щось пішло не так... Спробуйте /start чи /play');
        }
//        $bot->invoke(PlayHandler::class);
    }
}
