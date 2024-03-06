<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;
use App\Http\Controllers\Telegram\WeebhookController;
/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

Route::post('/telegram-webhook', WeebhookController::class);

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage('Hello, world!');
})->description('The start command!');

$bot->onException(\DomainException::class, function (Nutgram $bot, \Throwable $exception) {
    $bot->sendMessage(sprintf('Error: %s', $exception->getMessage()));
});

$bot->onException(function (Nutgram $bot, \Throwable $exception) {
    $bot->sendMessage('Something went wrong...');
});

$bot->fallback(function (Nutgram $bot) {
    $bot->sendMessage(sprintf(
        'Can\'t understand ya, %s. Your message was: "%s"',
        $bot->message()?->from?->first_name,
        $bot->message()?->text,
    ));
});
