<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('global-setting-updated.{adminId}', function ($user, $adminId) {
    return (int) $user->admin_id === (int) $adminId;
});

Broadcast::channel('tables-updated.{adminId}', function ($user, $adminId) {
    return (int) $user->admin_id === (int) $adminId;
});

Broadcast::channel('order-status-history-created.admin.{adminId}.check.{checkId}', function ($user, $adminId, $checkId) {
            logger()->info('Broadcast auth hit', [
            'user_id' => $user?->id,
            'admin_id_user' => $user?->admin_id,
            'adminId_param' => $adminId,
            'checkId_param' => $checkId,
        ]);

        //return true; // 🔴 teste
    return (int) $user->admin_id === (int) $adminId;
});
