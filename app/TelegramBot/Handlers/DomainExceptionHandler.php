<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Handlers\PlayHandler;

class DomainExceptionHandler
{
    public function __invoke(Nutgram $bot, \Throwable $exception)
    {
        $bot->sendMessage(sprintf('Error: %s', $exception->getMessage()));
        $bot->invoke(PlayHandler::class);
    }
}
