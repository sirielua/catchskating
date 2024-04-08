<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;

class RegistrationMenu extends InlineMenu
{
    use PlayersTrait;
    
    public function start(Nutgram $bot): void
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        
        if (null !== $player) {
            $this->end();
            $bot->sendMessage('Ви вже зареєстровані!');
            $bot->invoke(Handlers\PlayerHandler::class, ['id' => $player->id]);
            return;
        }
        
        $this->setInitialMessage($bot);
        $this->clearButtons()
        ->addButtonRow(
            Keyboard\InlineKeyboardButton::make('Новий гравець', callback_data: '@register'),
            Keyboard\InlineKeyboardButton::make('Вже реєструвався/-лась', callback_data: '@returning'),
        )
        ->orNext('home')
        ->showMenu();
    }
    
    private function setInitialMessage(Nutgram $bot): void
    {
        $message =
        <<<END
        Реєстрація
        =======
        Для того щоб користувння ботом треба зареєструватись
        END;
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
    
    public function home(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\StartHandler::class);
    }
    
    public function register(Nutgram $bot): void
    {
        $this->end();
        RegistrationConversation::begin($bot);
    }
    
    public function returning(Nutgram $bot): void
    {
        $this->setRegistrationNotFoundMessage($bot);

        $this->clearButtons()
        ->addButtonRow(
            Keyboard\InlineKeyboardButton::make('Назад', callback_data: '@start'),
            Keyboard\InlineKeyboardButton::make('На головну', callback_data: '@home'),
        )
        ->orNext('home')
        ->showMenu();
    }
    
    private function setRegistrationNotFoundMessage(Nutgram $bot): void
    {
        $message = sprintf(
            <<<END
            Реєстрацію не знайдено
            =======
            Даний телеграм-аккаунт не прив'язаний до жодного гравця.
            Якщо ви впевнені що реєструвались раніше - перешліть це повідомлення адміністратору.

            *Телеграм ID*: %s
            *Ім'я*: %s %s
            *tg*: @%s
            END,
            $bot->userId(),
            $bot->user()->first_name,
            $bot->user()->last_name,
            $bot->user()->username,
        );
        
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
}
