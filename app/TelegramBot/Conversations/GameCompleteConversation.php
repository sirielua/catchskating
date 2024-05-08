<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use App\TelegramBot\Traits\GamesTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use Carbon\CarbonInterval;

class GameCompleteConversation extends Conversation
{
    use GamesTrait;
    
    private const MESSAGES = [
        'gameResult' => 
            <<<END
            *Пeремога*: %s
            *Час*: %s
            
            Вірно?
            \n
            END,
    ];
    
    private const CATCHERS = 'catchers';
    private const RUNNERS = 'runners';
    
    private const CONFIRM = 'confirm';
    private const DECLINE = 'decline';
    
    public int $gameId;
    public ?int $duration = null;
    public ?string $winner = null;
    
    public function start(Nutgram $bot, int $gameId)
    {
        $this->gameId = $gameId;
        $this->askWhoWon($bot);
    }
    
    private function askWhoWon(Nutgram $bot)
    {
        $bot->sendMessage(
            text: 'Хто переміг?',
            reply_markup: Keyboard\InlineKeyboardMarkup::make()
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('Доганяючі', callback_data: self::CATCHERS),
                    Keyboard\InlineKeyboardButton::make('Втікаючі', callback_data: self::RUNNERS),
                )
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('Назад', callback_data: 'viewSession'),
                ),
        );
        
        $this->next('handleWhoWon');
    }
    
    public function handleWhoWon(Nutgram $bot)
    {
        $game = $this->getGame($this->gameId);
        
        if (!$bot->isCallbackQuery()) {
            $this->askWhoWon($bot);
            return;
        }
        
        if ('viewSession' === $bot->callbackQuery()->data) {
            $this->end();
            SessionGameMenu::begin($bot, data: ['sessionId' => $game->session_id]);
            return;
        }
        
        if (!in_array($bot->callbackQuery()->data, [self::CATCHERS, self::RUNNERS])) {
            $this->askWhoWon($bot);
            return;
        }
        
        $this->winner = $bot->callbackQuery()->data;
        
        $this->askDuration($bot);
    }
    
    private function askDuration(Nutgram $bot)
    {
        $game = $this->getGame($this->gameId);
        $bot->sendMessage(
            text: 'Підтвердіть час, або введіть вручну (секундах або хвилини та секунди через двокрапку)',
            reply_markup: Keyboard\InlineKeyboardMarkup::make()
                ->addRow(
                    Keyboard\InlineKeyboardButton::make(CarbonInterval::seconds($game->duration)
                        ->cascade()
                        ->forHumans(), 
                    callback_data: $game->duration.'@'.'handleDuration'),
                )
        );
        
        $this->next('handleDuration');
    }
    
    public function handleDuration(Nutgram $bot)
    {
        if ($bot->isCallbackQuery()) {
            $this->duration = (int)$bot->callbackQuery()->data;
        } else {
            $this->duration = $this->parseDuration($bot->message()?->text);
        }
        
        if (empty($this->duration)) {
            $this->askDuration($bot);
            return;
        }
        
//        $this->confirm($bot);
        $this->next('handleCompleteGame');
    }
    
    private function parseDuration($input): ?int
    {
        if (is_numeric($input)) {
            return (int)$input;
        }
        
        if (strpos($input, ':') !== false) {
            list($minutes, $seconds) = explode(':', $input, 2);
            return $minutes*60 + $seconds;
        }
        
        return null;
    }
    
//    private function confirm(Nutgram $bot)
//    {
//        $bot->sendMessage(
//            text: sprintf(
//                self::MESSAGES['gameResult'],
//                $this->winner === self::CATCHERS ? 'Доганяючі' : 'Втікаючі',
//                CarbonInterval::seconds($this->duration)
//                    ->cascade()
//                    ->forHumans(),
//            ),
//            parse_mode: 'markdown',
//            reply_markup: Keyboard\InlineKeyboardMarkup::make()
//                ->addRow(
//                    Keyboard\InlineKeyboardButton::make('Так', callback_data: self::CONFIRM),
//                    Keyboard\InlineKeyboardButton::make('Ні', callback_data: self::DECLINE),
//                )
//        );
//        
//        $this->next('handleCompleteGame');
//    }
    
    public function handleCompleteGame(Nutgram $bot)
    {
        if (!$bot->isCallbackQuery()) {
            $this->askWhoWon($bot);
            return;
        }
        
        if ((self::DECLINE === $bot->callbackQuery()->data) || empty($this->duration) || empty($this->winner)) {
            $this->askWhoWon($bot);
            return;
        }
        
        $this->end();
        
        $this->completeGame($this->gameId, $this->winner === self::CATCHERS, $this->duration);
        
        $game = $this->getGame($this->gameId);
        SessionGameMenu::begin($bot, data: ['sessionId' => $game->session_id]);
    }
}
