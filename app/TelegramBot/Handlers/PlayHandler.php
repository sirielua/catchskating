<?php

namespace App\TelegramBot\Handlers;

use App\TelegramBot\Traits\PlayersTrait;
use App\TelegramBot\Traits\SessionsTrait;
use SergiX44\Nutgram\Nutgram;
use App\TelegramBot\Conversations;

class PlayHandler
{
    use PlayersTrait, SessionsTrait;
    
    public function __invoke(Nutgram $bot)
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        if (null === $player) {
            Conversations\StartMenu::begin($bot);
            return;
        }
        
        $session = $this->getActualSession($player->id);
        if (null === $session) {
            Conversations\SessionsMenu::begin($bot);
            return;
        }
        
        if (!$session->isActive()) {
            Conversations\SessionMenu::begin($bot, data: ['sessionId' => $session->id]);
        }
        
        Conversations\SessionGameMenu::begin($bot, data: ['sessionId' => $session->id]);
    }
}
