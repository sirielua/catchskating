<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Player\Models\Player;
use Carbon\CarbonInterval;

class StartMenu extends InlineMenu
{
    use PlayersTrait;
    
    public function start(Nutgram $bot): void
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        
        $this->clearButtons();
        if (null === $player) {
            $this->setGuestWelcomeMessage($bot);
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Реєстрація', callback_data: '@register'),
                Keyboard\InlineKeyboardButton::make('Правила', callback_data: '@rules'),
            );
        } else {
            $this->setPlayerWelcomeMessage($bot, $player);
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Статистика', callback_data: '@stats'),
                Keyboard\InlineKeyboardButton::make('Правила', callback_data: '@rules'),
            );
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Грати', callback_data: '@play'),
                Keyboard\InlineKeyboardButton::make('Сессії', callback_data: '@sessions'),
            );
        }

        $this->orNext('none')
        ->showMenu();
    }
    
    public function stats(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\PlayerHandler::class);
    }
    
    public function rules(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\RulesHandler::class);
    }
    
    public function play(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\PlayHandler::class);
    }
    
    public function sessions(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\SessionsHandler::class);
    }
    
    public function register(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\RegistrationHandler::class);
    }
    
    public function none(Nutgram $bot)
    {
        $this->end();
    }
    
    private function setPlayerWelcomeMessage(Nutgram $bot, Player $player): void
    {
        $inactiveTime = $player->last_played_at ?
            CarbonInterval::seconds(time() - $player->last_played_at->getTimestamp())
                ->cascade()
                ->forHumans()
            : 0;
        
        $totalTime = ($player->time_catching + $player->time_running) ? 
            CarbonInterval::seconds($player->time_catching + $player->time_running)
                ->cascade()
                ->forHumans()
            : 0;
        
        $message = 
            <<<END
            Вітаю, *$player->name* #$player->id
            =======
            \n
            END;
        
        if ($player->last_played_at) {
            $inactiveTime = CarbonInterval::seconds(time() - $player->last_played_at->getTimestamp())
                ->cascade()
                ->forHumans();
            
            $message .=
            <<<END
            Ви не грали в чаклуни вже $inactiveTime...
            \n
            END;
        }
        
        $message .=
            <<<END
            *Зіграно матчей*: $player->games_total
            *Сумарний час*: $totalTime
            END;
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
    
    private function setGuestWelcomeMessage(Nutgram $bot): void
    {
        $message = 
            <<<END
            Вітаю в світі чаклунів, *{$bot->user()->first_name}*!
            =======
            
            Для початку тобі треба зареєструватись.
            END;
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
}
