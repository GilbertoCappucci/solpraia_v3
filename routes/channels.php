<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('global-setting-updated.{adminId}', function ($user, $adminId) {
    return (int) $user->user_id === (int) $adminId;
});

Broadcast::channel('tables-updated.{userId}', function ($user, $userId) {
    return (int) $user->user_id === (int) $userId;
});
