<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\SessionsTrait;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Game\Models\Session;
use App\Modules\Game\Models\SessionPlayer;
use App\Modules\Game\Models\PlayerCondition;
use App\Modules\Player\Models\Player;
use App\TelegramBot\Helpers\MarkdownHelper;
use Carbon\CarbonInterval;

class SessionPlayerMenu extends InlineMenu
{
    use SessionsTrait, PlayersTrait;
    
    private const MESSAGES = [
        'info' => 
            <<<END
            *Сессія #%s*:
            *#%s %s* %s
            -----------
            \n
            END,
        'streaks' => 
            <<<END
            *Серії*
            -----------
            *Ловить*: %s
            *Втікає*: %s
            *Відпочиває*: %s
            \n
            END,
    ];
    
    public int $sessionId;
    public int $playerId;
    
    public function start(Nutgram $bot, int $sessionId, int $playerId)
    {
        $session = $this->getSession($sessionId);
        $sessionPlayer = $session?->players()
            ->where('player_id', $playerId)
            ->first();
        
        if (null === $sessionPlayer) {
            $this->end();
            $bot->invoke(Handlers\PlayHandler::class);
            return;
        }
        
        $this->sessionId = $sessionId;
        $this->playerId = $playerId;
        
        $this->setInfoMessage($bot, $session, $sessionPlayer); 
        $this->configureButtons();
        $this->orNext('none')->showMenu();
    }
    
    private function setInfoMessage(Nutgram $bot, Session $session, SessionPlayer $sessionPlayer): void
    {
        $message = sprintf(
            self::MESSAGES['info'],
            $session->id,
            $sessionPlayer->player_id,
            MarkdownHelper::escape($sessionPlayer->name),
            $sessionPlayer->condition->emoji(),
        );
        
        $message .= sprintf(
            self::MESSAGES['streaks'],
            $sessionPlayer->catching_streak,
            $sessionPlayer->running_streak,
            $sessionPlayer->resting_streak,
        );
        
        if ($sessionPlayer->player) {
            $message .= $this->getPlayerStatsMessage($sessionPlayer->player);
        }
        $this->menuText(
            text: $message,
            opt: ['parse_mode' => 'markdown'],
        );
    }
    
    private function getPlayerStatsMessage(Player $player): string
    {
        $winPercent = $player->games_total ? round(100*$player->wins_total/$player->games_total) : 0;
        $losePercent = $player->games_total ? 100 - $winPercent : 0;
        $totalTime = ($player->time_catching + $player->time_running) ? 
            CarbonInterval::seconds($player->time_catching + $player->time_running)
                ->cascade()
                ->forHumans()
            : 0;
        
        $catcherWinPercent = $player->games_as_catcher ? round(100*$player->wins_as_catcher/$player->games_as_catcher) : 0;
        $catcherLosePercent = $player->games_as_catcher ? 100 - $catcherWinPercent : 0;
        $timeCatching = $player->time_catching ?
            CarbonInterval::seconds($player->time_catching)
                ->cascade()
                ->forHumans()
            : 0;
        
        $runnerWinPercent = $player->games_as_runner ? round(100*$player->wins_as_runner/$player->games_as_runner) : 0;
        $runnerLosePercent = $player->games_as_runner ? 100 - $runnerWinPercent : 0;
        $timeRunning = $player->time_running ?
            CarbonInterval::seconds($player->time_running)
                ->cascade()
                ->forHumans()
            : 0;
        
        return
            <<<END
            Статистика
            -----------
            *Зіграно матчей*: $player->games_total
            *Перемоги*: $player->wins_total ($winPercent%)
            *Поразки*: $player->loses_total ($losePercent%)
            *Сумарний час*: $totalTime

            За доганяючих
            -----------
            *Зіграно матчей*: $player->games_as_catcher
            *Перемоги*: $player->wins_as_catcher ($catcherWinPercent%)
            *Поразки*: $player->loses_as_catcher ($catcherLosePercent%)
            *Сумарний час*: $timeCatching

            За втікаючих
            -----------
            *Зіграно матчей*: $player->games_as_runner
            *Перемоги*: $player->wins_as_runner ($runnerWinPercent%)
            *Поразки*: $player->loses_as_runner ($runnerLosePercent%)
            *Сумарний час*: $timeRunning
            END;
    }
    
    private function configureButtons()
    {
        $this->clearButtons();
        
        $this->addButtonRow(
            Keyboard\InlineKeyboardButton::make('Злитись', callback_data: '@'.'handleLeaveSession'),
        );
        
        $this->addButtonRow(
            Keyboard\InlineKeyboardButton::make(PlayerCondition::Catching->emoji().'Ловить', callback_data: PlayerCondition::Catching->value.'@'.'handleCondition'),
            Keyboard\InlineKeyboardButton::make(PlayerCondition::Ready->emoji().'Ready', callback_data: PlayerCondition::Ready->value.'@'.'handleCondition'),
            Keyboard\InlineKeyboardButton::make(PlayerCondition::Running->emoji().'Втікає', callback_data: PlayerCondition::Running->value.'@'.'handleCondition'),
            Keyboard\InlineKeyboardButton::make(PlayerCondition::Resting->emoji().'Відпочиває', callback_data: PlayerCondition::Resting->value.'@'.'handleCondition'),
        );
        
        $this->addButtonRow(
            Keyboard\InlineKeyboardButton::make('Назад', callback_data: '@'.'viewSession'),
        );
    }
    
    public function handleLeaveSession(Nutgram $bot)
    {
        $this->leaveSession($this->sessionId, $this->playerId);
        $this->end();
        $bot->invoke(Handlers\SessionHandler::class, ['id' => $this->sessionId]);
    }
    
    public function handleCondition(Nutgram $bot)
    {
        $newCondition = PlayerCondition::from($bot->callbackQuery()?->data);
        
        $player = $this->getSession($this->sessionId)?->players()
            ->where('player_id', $this->playerId)
            ->firstOrFail();
        
        if ($newCondition !== $player->condition) {
            $this->updateCondition(
                $player->session_id,
                $player->player_id,
                $newCondition->value,
            );
//            $this->start($bot, $this->sessionId, $this->playerId);
        }
        $this->viewSession($bot);
    }

    public function viewSession(Nutgram $bot)
    {
        $this->end();
        SessionGameMenu::begin($bot, data: ['sessionId' => $this->sessionId]);
    }
    
    public function none(Nutgram $bot)
    {
        $bot->sendMessage('Bye!');
        $this->end();
    }
}
