<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('notification.{userId}', function ($user, $userId) {
    return Auth::check() && (int) $user->id === (int) $userId;
});
Broadcast::channel('chat.user.{userId}', function ($user, $userId) {
    return Auth::check() && (int) $user->id === (int) $userId;
});