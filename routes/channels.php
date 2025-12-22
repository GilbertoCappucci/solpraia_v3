<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('global-setting-updated.{adminId}', function ($user, $adminId) {
    return $user->user_id == $adminId;
});
