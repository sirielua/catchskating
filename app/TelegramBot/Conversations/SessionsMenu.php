<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use Illuminate\Contracts\Pagination\Paginator;
use App\TelegramBot\Traits\SessionsTrait;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Game\Models\Session;
use App\Modules\Player\Models\Player;

class SessionsMenu extends InlineMenu
{
    use SessionsTrait, PlayersTrait;
    
    public int $page = 1;
    
    private const MESSAGES = [
        'noSessions' => 
            <<<END
            *Наразі немає актуальних ігрових сессій*:
            \n
            END,
        'sessionsHeader' =>
            <<<END
            *Актуальні ігрові сесії*:
            \n
            END,
        'sessionsRow' =>
            <<<END
            Дата: %s
            Опис: %s
            \n
            END,
        'organizerRightsRequired' =>
            <<<END
            Щоб створити сессію треба мати права організатора
            END,
    ];
    
    public function start(Nutgram $bot): void
    {
        if ($bot->isCallbackQuery()) {
            $this->page = (int)$bot->callbackQuery()->data ?: 1;
        }
        
        $sessions = $this->getAvailableSessions($this->page, limit: 5);
        $player = $this->getPlayerByTelegram($bot->userId());
        
        $this->setMessage($bot, $sessions->items(), $player);
        $this->clearButtons();
        
        foreach ($sessions as $session) {
            $this->addSessionButtons($session, $player);
        }
        $this->pagiantion($sessions);
        
        if ($player?->user?->canOrganizeGames()) {
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Нова сессія', callback_data: '@createSession'),
            );
        }
        
        $this->addButtonRow(
            Keyboard\InlineKeyboardButton::make('На головну', callback_data: '@home'),
        )
        ->orNext('home')
        ->showMenu();
    }
    
    private function addSessionButtons(Session $session, ?Player $player = null): void
    {
        $buttons = [
            Keyboard\InlineKeyboardButton::make($session->date->format('d-m-Y H:i'), callback_data: $session->id.'@viewSession'),
        ];

        if (!$session->isEnded() && $player) {
            if (!$session->hasPlayer($player)) {
                $buttons[] = Keyboard\InlineKeyboardButton::make('Доєднатись', callback_data: $session->id.'@handleJoinSession');
            } else {
                $buttons[] = Keyboard\InlineKeyboardButton::make('Злитись', callback_data: $session->id.'@handleLeaveSession');
            }
        }

        $this->addButtonRow(...$buttons);
    }
    
    private function pagiantion(Paginator $sessions): void
    {
        if (false === $sessions->hasPages()) {
            return;
        }
        
        $buttons = [];
        if ($sessions->currentPage() > 1) {
            $buttons[] = Keyboard\InlineKeyboardButton::make('< Сюди', callback_data: ($sessions->currentPage() - 1).'@start');
        }
        
        if ($sessions->hasMorePages()) {
            $buttons[] = Keyboard\InlineKeyboardButton::make('Туди >', callback_data: ($sessions->currentPage() + 1).'@start');
        }
        
        $this->addButtonRow(...$buttons);
    }
    
    public function createSession(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\CreateSessionHandler::class);
    }
    
    public function viewSession(Nutgram $bot)
    {
        $this->end();
        
        
        $sessionId = (int)$bot->callbackQuery()->data;
        if ($sessionId) {
            $bot->invoke(Handlers\SessionHandler::class, ['id' => $sessionId]);
        } else {
            $bot->invoke(Handlers\PlayHandler::class);
        }
    }
    
    public function handleJoinSession(Nutgram $bot)
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        if ($player && $bot->isCallbackQuery()) {
            $this->joinSession((int)$bot->callbackQuery()->data, $player->id);
            $bot->callbackQuery()->data = $this->page;
        }
        
        $this->start($bot);
    }
    
    public function handleLeaveSession(Nutgram $bot)
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        if ($player && $bot->isCallbackQuery()) {
            $this->leaveSession((int)$bot->callbackQuery()->data, $player->id);
            $bot->callbackQuery()->data = $this->page;
        }
        
        $this->start($bot);
    }
    
    public function home(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\StartHandler::class);
    }
    
    private function setMessage(Nutgram $bot, array $sessions = [], ?Player $player = null): void
    {
        if (count($sessions) === 0) {
            $message = self::MESSAGES['noSessions'];
        } else {
            $message = self::MESSAGES['sessionsHeader'];
            
            foreach ($sessions as $session) {
                $message .= sprintf(
                    self::MESSAGES['sessionsRow'],
                    $session->date->format('d-m-Y H:i'),
                    $session->description,
                );
            }
        }
        
        if (!$player?->user?->canOrganizeGames()) {
            $message .= self::MESSAGES['organizerRightsRequired'];            
        }
        
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
}
