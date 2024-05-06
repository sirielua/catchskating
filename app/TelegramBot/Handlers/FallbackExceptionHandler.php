<?php

namespace App\TelegramBot\Handlers;

use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\App;

class FallbackExceptionHandler
{
    public function __invoke(Nutgram $bot, \Throwable $exception)
    {
        if (App::isLocal() && App::hasDebugModeEnabled()) {
            throw $exception;
        } elseif (App::hasDebugModeEnabled()) {
            $bot->sendMessage(sprintf('%s. File: %s. Line: %s', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
        } else {
            $bot->sendMessage('Щось пішло не так... Спробуйте /start чи /play');
        }
//        $bot->invoke(PlayHandler::class);
    }
}
