<?php

namespace App\TelegramBot\Traits;

use App\MessageBus\DispatchesMessagesTrait;
use App\Modules\Game\Requests\Session\Queries;
use App\Modules\Game\Requests\Session\Commands;
use App\Modules\Game\Models\Session;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait SessionsTrait
{
    use DispatchesMessagesTrait;
    
    public function getSession(int $id): ?Session
    {
        try {
            return $this->dispatchMessage(new Queries\GetSession($id));
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    
    public function getAvailableSessions(int $page = 1, ?int $limit = null): Paginator
    {
        try {
            return $this->dispatchMessage(
                new Queries\GetAvailableSessions($page, $limit)
            );
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    
    public function createSession(\DateTimeImmutable $date, ?string $description = null): Session
    {
        return $this->dispatchMessage(new Commands\CreateSession(
            $date,
            $description,
        ));
    }
    
    public function startSession($id): Session
    {
        return $this->dispatchMessage(new Commands\StartSession($id));
    }
    
    public function endSession($id): Session
    {
        return $this->dispatchMessage(new Commands\EndSession($id));
    }
    
    public function continueSession($id): Session
    {
        return $this->dispatchMessage(new Commands\ContinueSession($id));
    }
}
