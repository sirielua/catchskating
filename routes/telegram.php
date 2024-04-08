<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\TelegramBot\Handlers;
/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', Handlers\StartHandler::class)->description('The start command!');
$bot->onCommand('rules', Handlers\RulesHandler::class)->description('Rules command!');

$bot->onCommand('registration', Handlers\RegistrationHandler::class)
    ->description('Register new player');
$bot->onCommand('register', Handlers\RegistrationHandler::class)
    ->description('Register new player');

$bot->onCommand('player {id}', Handlers\PlayerHandler::class)
    ->whereNumber('id')
    ->description('View player profile');
$bot->onCommand('player', Handlers\PlayerHandler::class)
    ->description('View your player profile');

$bot->onCommand('newsession', Handlers\CreateSessionHandler::class)
    ->description('Create new game session');
$bot->onCommand('sessions', Handlers\SessionsHandler::class)
    ->description('View available game sessions');
$bot->onCommand('session {id}', Handlers\SessionHandler::class)
    ->whereNumber('id')
    ->description('View your selected game session');
$bot->onCommand('play', Handlers\PlayHandler::class)
    ->description('View your current game session');

$bot->onException(\DomainException::class, Handlers\DomainExceptionHandler::class);
$bot->onException(Handlers\FallbackExceptionHandler::class);
$bot->fallback(Handlers\FallbackHandler::class);
