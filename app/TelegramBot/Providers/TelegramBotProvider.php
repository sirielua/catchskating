<?php

namespace App\TelegramBot\Providers;

use Illuminate\Support\ServiceProvider;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;

class TelegramBotProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        Conversation::refreshOnDeserialize();
//        $bot = $this->app->get(Nutgram::class);
    }
}
