<?php

namespace App\TelegramBot\Conversations;

use SergiX44\Nutgram\Conversations\InlineMenu;
use App\TelegramBot\Traits\SessionsTrait;
use App\TelegramBot\Traits\PlayersTrait;
use App\TelegramBot\Traits\GamesTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard;
use App\TelegramBot\Handlers;
use App\Modules\Game\Models\Session;
use App\Modules\Game\Models\SessionPlayer;
use App\Modules\Game\Models\PlayerCondition;
use App\Modules\Game\Models\Game;
use App\Modules\Game\Models\GamePlayer;
use App\Modules\Game\Models\GameWinner;
use Carbon\CarbonInterval;

class SessionGameMenu extends InlineMenu
{
    use SessionsTrait, PlayersTrait, GamesTrait;
    
    private const MESSAGES = [
        'infoClear' => 
            <<<END
            *Сессія #%s*:
            -----------
            \n
            END,
        'infoGame' => 
            <<<END
            *Сессія #%s*, *Гра #%s* \[%s]:
            -----------
            
            *Доганяють* [%s]:
            %s
            
            *Втікають* [%s]:
            %s
             -----------
            \n
            END,
        'gameResult' => 
            <<<END
            *Переможець*: %s
            *Час*: %s
             -----------
            \n
            END,
        'legend' => 
            <<<END
            *Підказка*:
            %s Ловить, %s Ready, %s Втікає, %s Відпочиває
            END,
        
    ];
    
    public int $sessionId;
    
    public function start(Nutgram $bot, int $sessionId)
    {
        $session = $this->getSession($sessionId);
        if (null === $session) {
            $this->end();
            $bot->invoke(Handlers\SessionsHandler::class);
            return;
        }
        
        $this->sessionId = $sessionId;
        $this->setInfoMessage($bot, $session);
        
        $this->configureButtons($session);
        $this->orNext('none')          
            ->showMenu();
    }
    
    private function setInfoMessage(Nutgram $bot, Session $session): void
    {
        $game = $session->games->last();
        if (null == $game) {
            $message = sprintf(
                self::MESSAGES['infoClear'],
                $session->id,
            );
        } else {
            $message = sprintf(
                self::MESSAGES['infoGame'],
                $session->id,
                $game->id,
                $game->status->value,
                $game->catchers_count,
                implode(', ', $game->catchers->map(function (GamePlayer $player) {
                    return $player->name;
                })->all()),
                $game->runners_count,
                implode(', ', $game->runners->map(function (GamePlayer $player) {
                    return $player->name;
                })->all()),
            );
                
            if ($game->isCompleted()) {
                $message .= sprintf(
                    self::MESSAGES['gameResult'],
                    $game->winner === GameWinner::Catchers ? 'Доганяючі' : 'Втікаючі',
                    CarbonInterval::seconds($game->duration)
                        ->cascade()
                        ->forHumans(),
                );
            } elseif ($game->calculateDuration()) {
                $message .= sprintf(
                    self::MESSAGES['gameResult'],
                    'Не визначений',
                    CarbonInterval::seconds($game->calculateDuration())
                        ->cascade()
                        ->forHumans(),
                );
            }  
        }
        
        $message .= sprintf(
            self::MESSAGES['legend'],
            PlayerCondition::Catching->emoji(),
            PlayerCondition::Ready->emoji(),
            PlayerCondition::Running->emoji(),
            PlayerCondition::Resting->emoji(),
        );
        $this->menuText($message, opt: ['parse_mode' => 'markdown']);
    }
    
    private function configureButtons(Session $session): void
    {       
        $this->clearButtons();
        
        $this->addPlayersConditionButtons($session);
        $this->addGameButtons($session);
    }
    
    private function addPlayersConditionButtons(Session $session)
    {
        foreach ($session->players->chunk(2) as $chunk) {
            $pair = $chunk->values();
            
            $playerButtons = $conditionButtons = [];

            $playerButtons[] = $this->getPlayerButton($pair->get(0));
            $conditionButtons = array_merge($conditionButtons, $this->getConditionButtons($pair->get(0)));

            if (null !== $pair->get(1)) {
                $playerButtons[] = $this->getPlayerButton($pair->get(1));
                $conditionButtons = array_merge($conditionButtons, $this->getConditionButtons($pair->get(1)));
            } else {
                $playerButtons[] = Keyboard\InlineKeyboardButton::make('_', callback_data: '@'.'refresh');
                $conditionButtons = array_merge($conditionButtons, [
                    Keyboard\InlineKeyboardButton::make('_', callback_data: '@'.'refresh'),
                    Keyboard\InlineKeyboardButton::make('_', callback_data: '@'.'refresh'),
                    Keyboard\InlineKeyboardButton::make('_', callback_data: '@'.'refresh'),
                ]);
            }

            $this->addButtonRow(...$playerButtons);
//            $this->addButtonRow(...$conditionButtons);
        }
    }
    
    private function getPlayerButton(SessionPlayer $player): Keyboard\InlineKeyboardButton
    {
        return Keyboard\InlineKeyboardButton::make(
            sprintf('%s %s', $player->condition->emoji(), $player->name),
            callback_data: $player->player_id.'@'.'player'
        );
    }
    
    private function getConditionButtons(SessionPlayer $player): array
    {
        return array_values(array_filter([
            PlayerCondition::Catching->value => Keyboard\InlineKeyboardButton::make(PlayerCondition::Catching->emoji(), callback_data: $player->player_id.':'.PlayerCondition::Catching->value.'@'.'handleCondition'),
            PlayerCondition::Ready->value => Keyboard\InlineKeyboardButton::make(PlayerCondition::Ready->emoji(), callback_data: $player->player_id.':'.PlayerCondition::Ready->value.'@'.'handleCondition'),
            PlayerCondition::Running->value => Keyboard\InlineKeyboardButton::make(PlayerCondition::Running->emoji(), callback_data: $player->player_id.':'.PlayerCondition::Running->value.'@'.'handleCondition'),
            PlayerCondition::Resting->value => Keyboard\InlineKeyboardButton::make(PlayerCondition::Resting->emoji(), callback_data: $player->player_id.':'.PlayerCondition::Resting->value.'@'.'handleCondition'),
        ], function ($key) use ($player) {
            return $key !== $player->condition->value;
        }, ARRAY_FILTER_USE_KEY));
    }
    
    private function addGameButtons(Session $session)
    {
        $currentGame = $session->games->last();
        
        if (!$currentGame?->isOngoing() && !$currentGame?->isStopped()) {
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Нова гра/Формат 🎭', callback_data: '@'.'handleDraft'), 
            );
        }
        
        if ($currentGame?->isCompleted() || $currentGame?->isAborted()) {
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Наступна гра 🎲', callback_data: '@'.'handleNext'),
                Keyboard\InlineKeyboardButton::make('Повтор 🔁', callback_data: '@'.'handleRepeat'),
            );
        }
        
        if ($currentGame?->isDraft()) {
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Reroll 🎲', callback_data: '@'.'handleReroll'),
                Keyboard\InlineKeyboardButton::make('Почати ▶️', callback_data: '@'.'handleStart'),
            );
        } elseif ($currentGame?->isOngoing()) {
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Стоп ⏸', callback_data: '@'.'handleStop'),
            );
        } elseif ($currentGame?->isStopped()) {
            $this->addButtonRow(
                Keyboard\InlineKeyboardButton::make('Продовжити ▶', callback_data: '@'.'handleResume'),
                Keyboard\InlineKeyboardButton::make('Перервати ⏹️', callback_data: '@'.'handleAbort'),
                Keyboard\InlineKeyboardButton::make('Завершити ✅', callback_data: '@'.'handleComplete'),
            );
        }
        
        $this->addButtonRow(
            Keyboard\InlineKeyboardButton::make('Сессія', callback_data: '@'.'viewSession'),
            Keyboard\InlineKeyboardButton::make('Оновити', callback_data: '@'.'refresh'),
        );
    }
    
    public function player(Nutgram $bot)
    {
        $this->end();
        
        $playerId = (int)$bot->callbackQuery()?->data;
        if (!$playerId) {
            $this->start($bot, $this->sessionId);
            return;
        }
        SessionPlayerMenu::begin($bot, data: ['sessionId' => $this->sessionId, 'playerId' => $playerId]);
    }
    
    public function handleCondition(Nutgram $bot)
    {
        list($playerId, $condition) = explode(':', $bot->callbackQuery()->data);
        $newCondition = PlayerCondition::from($condition);
        
        $player = $this->getSession($this->sessionId)?->players()
            ->where('player_id', $playerId)
            ->firstOrFail();
        
        if ($newCondition !== $player->condition) {
            $this->updateCondition(
                $player->session_id,
                $player->player_id,
                $newCondition->value,
            );
            $this->start($bot, $this->sessionId);
        }
    }
    
    public function handleDraft(Nutgram $bot)
    {
        $this->end();
        GameDraftConversation::begin($bot, data: ['sessionId' => $this->sessionId]);
    }
    
    public function handleNext(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->draftGame(
            $game->session_id,
            $game->catchers_count,
            $game->runners_count,
        );
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleRepeat(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->repeatGame($game->id);
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleReroll(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->rerollGame($game->id);
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleStart(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->startGame($game->id);
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleStop(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->stopGame($game->id);
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleResume(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->resumeGame($game->id);
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleAbort(Nutgram $bot)
    {
        $this->end();
        
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        $this->abortGame($game->id);
        
        $this->start($bot, $this->sessionId);
    }
    
    public function handleComplete(Nutgram $bot)
    {
        $this->end();
        $session = $this->getSession($this->sessionId);
        $game = $session->games->last();
        GameCompleteConversation::begin($bot, data: ['gameId' => $game->id]);
    }
    
    public function viewSession(Nutgram $bot)
    {
        $this->end();
        SessionMenu::begin($bot, data: ['sessionId' => $this->sessionId]);
    }
    
    public function refresh(Nutgram $bot)
    {
        $this->end();
        $bot->sendMessage('Перезавантаження');
        $this->start($bot, $this->sessionId);
    }
    
    public function none(Nutgram $bot)
    {
        $bot->sendMessage('Bye!');
        $this->end();
    }
}
