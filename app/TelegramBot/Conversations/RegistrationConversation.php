<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use App\TelegramBot\Traits\PlayersTrait;
use App\MessageBus\DispatchesMessagesTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Player\Models\Player;
use App\Modules\User\Requests\Commands as UserCommands;

class RegistrationConversation extends Conversation
{
    use PlayersTrait, DispatchesMessagesTrait;
    
    private const CONFIRM = '1';
    
    private const DECLINE = '0';
    
    public ?string $playerName;
    
    public function start(Nutgram $bot)
    {
        $replyMarkUp = Keyboard\InlineKeyboardMarkup::make();
        
        $suggestions = array_filter(array_unique([
            $bot->user()->username,
            $bot->user()->first_name,
            $bot->user()->first_name.' '.$bot->user()->last_name,
        ]));
        
        foreach ($suggestions as $suggestion) {
            $replyMarkUp->addRow(
                Keyboard\InlineKeyboardButton::make($suggestion, callback_data: $suggestion)
            );
        }
        
        $bot->sendMessage(
            text: 'Виберіть ім\'я з наведених або введіть інше',
            reply_markup: $replyMarkUp,
        );
        
        $this->next('confirm');
    }

    public function confirm(Nutgram $bot)
    {
        $this->playerName = $bot->isCallbackQuery() ?
            $bot->callbackQuery()->data : $bot->message()?->text;
        
        if (empty($this->playerName)) {
            $this->start($bot);
            return;
        }
        
        $bot->sendMessage(
            text: 'Вас звати *'.$this->playerName.'*. Вірно?',
            parse_mode: 'markdown',
            reply_markup: Keyboard\InlineKeyboardMarkup::make()
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('Так', callback_data: self::CONFIRM),
                    Keyboard\InlineKeyboardButton::make('Ні', callback_data: self::DECLINE),
                )
        );
        
        $this->next('registration');
    }
    
    public function registration(Nutgram $bot)
    {
        if (!$bot->isCallbackQuery()) {
            $this->confirm($bot);
            return;
        }
        
        if ((self::DECLINE === $bot->callbackQuery()->data) || empty($this->playerName)) {
            $this->start($bot);
            return;
        }
        
        $this->end();
        
        $player = $this->getPlayerByTelegram($bot->userId());
        
        if (null !== $player) {
            $bot->sendMessage('Ви вже зареєстровані!');
        } else {
            $player = $this->createPlayer($bot);
            $bot->sendMessage('Ви успішно зареєструвались');
        }
        $bot->invoke(Handlers\PlayerHandler::class, ['id' => $player->id]);
    }
    
    private function createPlayer(Nutgram $bot): Player
    {
        $telegramUser = $this->dispatchMessage(new UserCommands\RegisterTelegramUser(
            $bot->userId(),
            $bot->user()->username,
            $bot->user()->first_name,
            $bot->user()->last_name,
            $this->playerName,
        ));
        return $telegramUser->user->player;
    }
}
