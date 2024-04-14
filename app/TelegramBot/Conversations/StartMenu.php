<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Player\Models\Player;
use App\TelegramBot\Helpers\MarkdownHelper;
use Carbon\CarbonInterval;

class StartMenu extends InlineMenu
{
    use PlayersTrait;
    
    private const MESSAGES = [
        'guestWelcome' =>
           <<<END
            Вітаю в світі чаклунів, *%s*!
            =======
            
            Для початку тобі треба зареєструватись.
            END,
        'welcome' => 
            <<<END
            Вітаю, *%s* #%s
            =======
            \n
            END,
        'inactivityTime' =>
            <<<END
            Ви не грали в чаклуни вже %s...
            \n
            END,
        'shortSummary' =>
            <<<END
            *Зіграно матчей*: %s
            *Сумарний час*: %s
            END,
    ];
    
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
        
        $message = sprintf(
            self::MESSAGES['welcome'],
            MarkdownHelper::escape($player->name),
            $player->id,
        );
        
        if ($player->last_played_at) {
            $inactiveTime = CarbonInterval::seconds(time() - $player->last_played_at->getTimestamp())
                ->cascade()
                ->forHumans();
            
            $message .= sprintf(
                self::MESSAGES['inactivityTime'],
                $inactiveTime,
            );
        }
        
        $message .= sprintf(
            self::MESSAGES['shortSummary'],
            $player->games_total,
            $totalTime,
        );
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
    
    private function setGuestWelcomeMessage(Nutgram $bot): void
    {
        $message = sprintf(
            self::MESSAGES['guestWelcome'],
            MarkdownHelper::escape($bot->user()->first_name),
        );
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
}
