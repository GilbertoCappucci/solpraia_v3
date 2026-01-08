<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('global-setting-updated.{adminId}', function ($user, $adminId) {
    return (int) $user->admin_id === (int) $adminId;
});

Broadcast::channel('tables-updated.{adminId}', function ($user, $adminId) {
    return (int) $user->admin_id === (int) $adminId;
});

Broadcast::channel('order-status-history-created.{adminId}', function ($user, $adminId) {
    return (int) $user->admin_id === (int) $adminId;
});
