<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use App\TelegramBot\Traits\SessionsTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;

class CreateSessionConversation extends Conversation
{
    use SessionsTrait;
    
    private const CONFIRM = '1';
    
    private const DECLINE = '0';
    
    private const MESSAGES = [
        'askDate' => 'Коли граємо? Введіть дату та час (приклад, %s)',
        'invalidaDate' => 'Будь ласка, введіть коректну дату (приклад, %s)',
        'overdueDate' => 'Це ж було вже!',
        'askDescription' => 'Виберіть опис сессії або натисніть "Пропустити"',
        'skip' => 'Пропустити',
        'confirmation' => 
            <<<END
            Час: %s
        
            Опис: %s
            
            Все вірно?
            \n
            END,
        'success' => 'Ігрова сессія запланована',
    ];
    
    public ?\DateTimeImmutable $date = null;
    
    public ?string $sessionDescription = null;
    
    public function start(Nutgram $bot)
    {
        $bot->sendMessage(sprintf(self::MESSAGES['askDate'], $this->promptDate()));
        $this->next('handleDate');
    }
    
    private function promptDate(): string{
        return (new \DateTimeImmutable('next saturday 15:00'))->format('d-m-Y H:i');
    }
    
    public function handleDate(Nutgram $bot)
    {
        try {
            $this->date = new \DateTimeImmutable(trim($bot->message()?->text));
        } catch (\Throwable) {
            $this->date = null;
            $bot->sendMessage(sprintf(self::MESSAGES['invalidaDate'], $this->promptDate()));
            $this->start($bot);
            return;
        }
        
        if ($this->date < new \DateTimeImmutable()) {
            $bot->sendMessage(self::MESSAGES['overdueDate']);
            $this->start($bot);
            return;
        }
        
        $this->askDescription($bot);
    }
    
    private function askDescription(Nutgram $bot)
    {
        $bot->sendMessage(
            self::MESSAGES['askDescription'],
            reply_markup: Keyboard\InlineKeyboardMarkup::make()
            ->addRow(
                Keyboard\InlineKeyboardButton::make(self::MESSAGES['skip'], callback_data: 'skip'),
            )
        );
        
        $this->next('handleDescription');
    }
    
    public function handleDescription(Nutgram $bot)
    {
        $this->sessionDescription = !$bot->isCallbackQuery() ?
            trim($bot->message()?->text) : null;
        
        $this->askConfirmation($bot);
    }
    
    private function askConfirmation(Nutgram $bot)
    {
        if (empty($this->date)) {
            $this->start($bot);
            return;
        }
        
        $bot->sendMessage(
            text: sprintf(
                self::MESSAGES['confirmation'],
                $this->date?->format('d-m-Y H:i'),
                $this->sessionDescription,
            ),
            parse_mode: 'markdown',
            reply_markup: Keyboard\InlineKeyboardMarkup::make()
                ->addRow(
                    Keyboard\InlineKeyboardButton::make('Так', callback_data: self::CONFIRM),
                    Keyboard\InlineKeyboardButton::make('Ні', callback_data: self::DECLINE),
                )
        );
        
        $this->next('handleConfirmation');
    }
    
    public function handleConfirmation(Nutgram $bot)
    {
        if (!$bot->isCallbackQuery()) {
            $this->askConfirmation($bot);
            return;
        }
        
        if ((self::DECLINE === $bot->callbackQuery()->data) || empty($this->date)) {
            $this->start($bot);
            return;
        }
        
        $this->end();
        
        $session = $this->createSession($this->date, $this->sessionDescription);
        
        $bot->sendMessage(self::MESSAGES['success']);
        $bot->invoke(Handlers\SessionHandler::class, ['id' => $session->id]);
    }
}
