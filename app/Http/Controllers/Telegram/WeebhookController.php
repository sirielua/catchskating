<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\AbstractController;
use SergiX44\Nutgram\Nutgram;

class WeebhookController extends AbstractController
{
    /**
     * Handle the telegram webhook request.
     */
    public function __invoke(Nutgram $bot)
    {
        $bot->run();
    }
}
