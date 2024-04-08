<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\PlayersTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Player\Models\Player;
use Carbon\CarbonInterval;

class PlayerMenu extends InlineMenu
{
    use PlayersTrait;
    
    public function start(Nutgram $bot, ?int $id = null): void
    {
        if (null === $id) {
            $this->displayOrRegister($bot);
        } else {
            $this->displayOrError($bot, $id);
        }
    }
    
    private function displayOrRegister(Nutgram $bot): void
    {
        $player = $this->getPlayerByTelegram($bot->userId());
        if (null === $player) {
            $this->end();
            $bot->invoke(Handlers\RegistrationHandler::class);
            return;
        }
        
        $this->setPlayerInfoMessage($bot, $player);
        $this->clearButtons()
        ->addButtonRow(
            Keyboard\InlineKeyboardButton::make('На головну', callback_data: '@home'),
        )
        ->orNext('home')
        ->showMenu();
    }
        
    private function displayOrError(Nutgram $bot, int $id): void
    {
        $player = $this->getPlayer($id);
        if (null === $player) {
            $this->setPlayerNotFoundMessage($bot);
        } else {
            $this->setPlayerInfoMessage($bot, $player);
        }
        
        $this->clearButtons()
        ->addButtonRow(
            Keyboard\InlineKeyboardButton::make('На головну', callback_data: '@home'),
        )
        ->orNext('home')
        ->showMenu();
    }
    
    public function home(Nutgram $bot)
    {
        $this->end();
        $bot->invoke(Handlers\StartHandler::class);
    }
    
    private function setPlayerInfoMessage(Nutgram $bot, Player $player): void
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
        
        $message = 
            <<<END
            *$player->name* #$player->id

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
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
    
    private function setPlayerNotFoundMessage(Nutgram $bot): void
    {
        $this->menuText('Гравця не знайдено', opt: ['parse_mode' => 'markdown']);
    }
}
