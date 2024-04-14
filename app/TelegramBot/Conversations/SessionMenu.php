<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\SessionsTrait;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Game\Models\Session;
use App\Modules\Player\Models\Player;
use App\TelegramBot\Helpers\MarkdownHelper;

class SessionMenu extends InlineMenu
{
    use SessionsTrait, PlayersTrait;
    
    private const MESSAGES = [
        'info' => 
            <<<END
            *Сессія #%s* \[%s]:
            -----------
            
            *Час*: %s
            *Опис*: %s
            \n
            END,
        'startedAt' =>
            <<<END
            *Розпочата*: %s\n
            END,
        'endedAt' =>
            <<<END
            *Закінчена*: %s\n
            END,
        'players' =>
            <<<END
            
            *Гравці (%s)*:
            -----------\n
            END,
        'player' =>
            <<<END
            *%s* \[%s]\n
            END,
    ];
    
    public int $sessionId;
    
    public function start(Nutgram $bot, int $sessionId)
    {
        $session = $this->getSession($sessionId);
        $player = $this->getPlayerByTelegram($bot->userId());
        
        if (null === $session) {
            $this->end();
            $bot->invoke(Handlers\SessionsHandler::class);
            return;
        }
        
        $this->sessionId = $sessionId;
        
        $this->setSessionInfoMessage($bot, $session);
        
        $this->configureButtons($session, $player);
        $this->orNext('none')          
            ->showMenu();
    }
    
    private function setSessionInfoMessage(Nutgram $bot, Session $session): void
    {
        $message = sprintf(
            self::MESSAGES['info'],
            $session->id,
            $session->status->value,
            $session->date->format('d-m-Y H:i'),
            $session->description,
        );
        
        if ($session->started_at) {
            $message .= sprintf(
                self::MESSAGES['startedAt'],
                $session->started_at->format('d-m-Y H:i'),
            );
        }
        
        if ($session->ended_at) {
            $message .= sprintf(
                self::MESSAGES['endedAt'],
                $session->ended_at->format('d-m-Y H:i'),
            );
        }
        
        if ($session->players->count()) {
            $message .= sprintf(
                self::MESSAGES['players'],
                $session->players->count(),
            );
            
            foreach ($session->players as $player) {
                $message .= sprintf(
                    self::MESSAGES['player'],
                    MarkdownHelper::escape($player->name),
                    $player->condition->value,
                );
            }
        }
        $this->menuText(
            text: $message,
            opt: ['parse_mode' => 'markdown'],
        );
    }
    
    private function configureButtons(Session $session, ?Player $player = null): void
    {   
        $this->clearButtons();
             
        if ($session->isActive()) {
            $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
                'Гра!', callback_data: '@'.'game',
            ));
        }
        
        if (!$session->isEnded()) {
            if ($player && !$session->hasPlayer($player)) {
                $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
                    'Доєднатись', callback_data: '@'.'handleJoinSession',
                ));
            } elseif ($player && $session->hasPlayer($player)) {
                $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
                    'Злитись', callback_data: '@'.'handleLeaveSession',
                ));
            }
        }
        
        if ($session->isPending()) {
            $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
                'Розпочати сессію', callback_data: '@'.'handleStartSession',
            ));
        } elseif ($session->isActive()) {
            $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
                'Закінчити сессію', callback_data: '@'.'handleEndSession',
            ));
        } elseif ($session->isEnded() && $session->canBeContinued()) {
            $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
                'Продовжити сессію', callback_data: '@'.'handleContinueSession',
            ));
        }
        
        $this->addButtonRow(Keyboard\InlineKeyboardButton::make(
            'Назад', callback_data: '@'.'viewSessions',
        ));
    }
    
    public function game(Nutgram $bot)
    {
        $this->end();
        SessionGameMenu::begin($bot, data: ['sessionId' => $this->sessionId]);
    }
    
    public function handleJoinSession(Nutgram $bot)
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        if ($player) {
            $this->joinSession($this->sessionId, $player->id);
        }
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleLeaveSession(Nutgram $bot)
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        if ($player) {
            $this->leaveSession($this->sessionId, $player->id);
        }
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleStartSession(Nutgram $bot)
    {
        $this->startSession($this->sessionId);
        $this->start($bot, $this->sessionId);
    }
    
    public function handleEndSession(Nutgram $bot)
    {
        $this->endSession($this->sessionId);
        $this->start($bot, $this->sessionId);
    }
    
    public function handleContinueSession(Nutgram $bot)
    {
        $this->continueSession($this->sessionId);
        $this->start($bot, $this->sessionId);
    }
    
    public function viewSessions(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\SessionsHandler::class);
    }
    
    public function none(Nutgram $bot)
    {
        $bot->sendMessage('Bye!');
        $this->end();
    }
}
