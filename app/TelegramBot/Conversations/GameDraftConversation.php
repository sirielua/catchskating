<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use App\TelegramBot\Traits\SessionsTrait;
use App\TelegramBot\Traits\GamesTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;

class GameDraftConversation extends Conversation
{
use SessionsTrait, GamesTrait, PlayersTrait;
    
    private const CONFIRM = '1';
    private const DECLINE = '0';
    
    public int $sessionId;
    public ?int $catchers = null;
    public ?int $runners = null;
    
    public function start(Nutgram $bot, int $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->askFormat($bot);
    }
    
    private function askFormat(Nutgram $bot)
    {
        $bot->sendMessage(
            text: 'Оберіть формат гри з наведених або введіть кількість чаклунів і втікаючих двома/трьома цифрами, без пробілів (наприклад, 22 якщо будете грати 2 vs 2; 511 якщо будете грати 5 vs 11)',
            reply_markup: Keyboard\InlineKeyboardMarkup::make()
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('3 vs 6', callback_data: '36'),
                    Keyboard\InlineKeyboardButton::make('4 vs 8', callback_data: '48'),
                )
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('2 vs 5', callback_data: '25'),
                    Keyboard\InlineKeyboardButton::make('3 vs 7', callback_data: '37'),
                )
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('Відміна', callback_data: 'viewSession'),
                ),
        );
        
        $this->next('handleFormat');
    }
    
    public function handleFormat(Nutgram $bot)
    {
        $session = $this->getSession($this->sessionId);
        
        $raw = $bot->isCallbackQuery() ? $bot->callbackQuery()->data : $bot->message()?->text;
        
        if ('viewSession' === $raw) {
            $this->end();
            SessionGameMenu::begin($bot, data: ['sessionId' => $this->sessionId]);
            return;
        }
        
        $catchers = (int)substr($raw, 0, 1);
        $runners = (int)substr($raw, 1);
        if ($catchers <= 0 || $runners <= 0) {
            $bot->sendMessage('Введіть формат по-людськи...');
            $this->askFormat($bot);
            return;
        }
        
        if (($catchers + $runners) > $session->players->count()) {
            $bot->sendMessage('У вас немає стільки гравців, нажаль...');
            $this->askFormat($bot);
            return;
        }
        
        $this->catchers = $catchers;
        $this->runners = $runners;
        
//        $this->confirm($bot);
        $this->next('handleCreateDraft');
    }
    
//    private function confirm(Nutgram $bot)
//    {
//        $bot->sendMessage(
//            text: sprintf('Граємо *%s vs %s*. Вірно?', $this->catchers, $this->runners),
//            parse_mode: 'markdown',
//            reply_markup: Keyboard\InlineKeyboardMarkup::make()
//                ->addRow(
//                    Keyboard\InlineKeyboardButton::make('Так', callback_data: self::CONFIRM),
//                    Keyboard\InlineKeyboardButton::make('Ні', callback_data: self::DECLINE),
//                )
//        );
//        
//        $this->next('handleCreateDraft');
//    }
    
    public function handleCreateDraft(Nutgram $bot)
    {
        if (!$bot->isCallbackQuery()) {
            $this->askFormat($bot);
            return;
        }
        
        if ((self::DECLINE === $bot->callbackQuery()->data) || empty($this->catchers) || empty($this->runners)) {
            $this->askFormat($bot);
            return;
        }
        
        $this->end();
        
        $this->draftGame($this->sessionId, $this->catchers, $this->runners);
        $this->resetConditions();
        SessionGameMenu::begin($bot, data: ['sessionId' => $this->sessionId]);
    }
    
    private function resetConditions()
    {
        $players = $this->getSession($this->sessionId)?->players();
        if (!$players) {
            return;
        }
        
        foreach ($players as $player) {
            $this->updateCondition(
                $player->session_id,
                $player->player_id,
                PlayerCondition::Ready->value,
            );
        }
    }
}
