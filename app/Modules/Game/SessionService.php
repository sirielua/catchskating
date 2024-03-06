<?php

namespace App\Modules\Game;

use App\Modules\Game\Models;
use App\Modules\Game\Requests\Commands\Session as Commands;

class SessionService
{
    public function create(Commands\CreateSession $command): Models\Session
    {
        $session = new Models\Session([
            'name' => $command->name,
            'date' => $command->date,
        ]);
        
        $this->guardSessionIsNotScheduledInThePast($session);
        
        $session->save();
        return $session;
    }
    
    private function guardSessionIsNotScheduledInThePast(Models\Session $session): void
    {
        if (!$session->isPending()) {
            return;
        }
        
        if ($session->date < (new \DateTimeImmutable())) {
            throw new \DomaineException('Game session can\'t be scheduled in the past');
        }
    }
    
    public function update(Commands\UpdateSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        $session->fill(array_filter([
            'name' => $command->name,
            'date' => $command->date,
            'status' => Models\SessionStatus::from($command->status),
        ], function ($value) {
            return null !== $value;
        }));
        
        $this->guardSessionIsNotScheduledInThePast($session);
        $session->save();
        return $session;
    }
    
    public function addPlayers(Commands\AddPlayers $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        SessionPlayer::upsert([
            array_map(function ($playerId) use ($session) {
                return ['session_id' => $session->id, 'player_id' => $playerId];
            }, $command->players)
        ], ['session_id', 'player_id']);
        
        return $session;
    }
    
    public function RemovePlayers(Commands\RemovePlayers $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        SessionPlayer::where('session_id', $session->id)
            ->whereIn('player_id', $command->players)
            ->delete();
        
        return $session;
    }
    
    public function open(Commands\OpenSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        if (!$session->isActive()) {
            return $session;
        }
        
        if (!$session->isPending()) {
            throw new \DomaineException('Only pending sessions can be opened');
        }
        
        if ($session->date < (new \DateTimeImmutable('-1 day'))) {
            throw new \DomaineException('Session scheduled more than 1 day ago can\'t be opened');
        }
        
        $session->update([
            'status' => Models\SessionStatus::Active,
            'opened_at' => new \DateTimeImmutable(),
        ]);
    }
    
    public function close(Commands\CloseSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        if (!$session->isClosed()) {
            return $session;
        }
        
        $session->update([
            'status' => Models\SessionStatus::Closed,
            'closed_at' => new \DateTimeImmutable(),
        ]);
    }
    
    public function reopen(Commands\ReopenSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        if (!$session->isActive()) {
            return $session;
        }
        
        if (!$session->isClosed()) {
            throw new \DomaineException('Only closed sessions can be reopened');
        }
        
        if ($session->opened_at < (new \DateTimeImmutable('-1 day'))) {
            throw new \DomaineException('Game session opened more than 1 day ago can\'t be reopened');
        }
                
        $session->update([
            'status' => Models\SessionStatus::Active,
            'closed_at' => null,
        ]);
    }
}
