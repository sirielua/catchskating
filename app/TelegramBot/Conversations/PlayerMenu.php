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

class PlayerMenu extends InlineMenu
{
    use PlayersTrait;
    
    private const MESSAGES = [
        'statisticsHeader' => 
            <<<END
            *%s* #%s
            \n
            END,
        'statisticsGeneral' =>
            <<<END
            *Статистика*
        
            *Зіграно матчей*: %s
            *Перемоги*: %s (%s)
            *Поразки*: %s (%s)
            *Сумарний час*: %s
            \n
            END,
        'statisticsCatchers' =>
            <<<END
            *За доганяючих*
            
            *Зіграно матчей*: %s
            *Перемоги*: %s (%s)
            *Поразки*: %s (%s)
            *Сумарний час*: %s
            \n
            END,
        'statisticsRunners' =>
            <<<END
            *За втікаючих*
            
            *Зіграно матчей*: %s
            *Перемоги*: %s (%s)
            *Поразки*: %s (%s)
            *Сумарний час*: %s
            END,
    ];
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
        
        $message = sprintf(
            self::MESSAGES['statisticsHeader'],
            MarkdownHelper::escape($player->name),
            $player->id,
        );
        $message .= sprintf(
            self::MESSAGES['statisticsGeneral'],
            $player->games_total,
            $player->wins_total,
            $winPercent.'%',
            $player->loses_total,
            $losePercent.'%',
            $totalTime,
        );
        $message .= sprintf(
            self::MESSAGES['statisticsCatchers'],
            $player->games_as_catcher,
            $player->wins_as_catcher,
            $catcherWinPercent.'%',
            $player->loses_as_catcher,
            $catcherLosePercent.'%',
            $timeCatching,
        );
        $message .= sprintf(
            self::MESSAGES['statisticsRunners'],
            $player->games_as_runner,
            $player->wins_as_runner,
            $runnerWinPercent.'%',
            $player->loses_as_runner,
            $runnerLosePercent.'%',
            $timeRunning,
        );

        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
    
    private function setPlayerNotFoundMessage(Nutgram $bot): void
    {
        $this->menuText('Гравця не знайдено', opt: ['parse_mode' => 'markdown']);
    }
}
