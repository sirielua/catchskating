<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;

class RulesMenu extends InlineMenu
{
    use PlayersTrait;
    
    public function start(Nutgram $bot): void
    {
        $message = 
            <<<END
            Правила гри:
            =======
            
            *Чаклун завжди правий!*
            END;
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown'])
        ->addButtonRow(
            Keyboard\InlineKeyboardButton::make('На головну', callback_data: '@home'),
        )
        ->orNext('home')
        ->showMenu();
    }
    
    public function home(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\StartHandler::class);
    }
}
