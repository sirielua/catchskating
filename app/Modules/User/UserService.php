<?php

namespace App\Modules\User;

use App\Modules\User\Requests\Queries;
use App\Modules\User\Models;

class UserService
{
    public function getByTelegram(Queries\GetUserByTelegram $query): Models\User
    {
        return Models\TelegramUser::where('telegram_id', $query->id)
            ->firstOrFail()
            ->user;
    }
}
