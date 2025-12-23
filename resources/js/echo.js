import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const isSecure = (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: isSecure ? undefined : import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: isSecure ? import.meta.env.VITE_REVERB_PORT ?? 443 : undefined,
    forceTLS: isSecure,
    enabledTransports: ['ws', 'wss'],
});

// Wait for the DOM to be fully loaded before subscribing to channels
document.addEventListener('DOMContentLoaded', () => {

    if (window.adminId) {
        window.Echo.private(`global-setting-updated.${window.adminId}`)
            .listen('.global.setting.updated', (e) => {
                console.log('📡 Evento recebido:', e);
                Livewire.dispatch('global.setting.updated', e.globalSettingUpdated);
            });
        console.log(`Echo is listening on channel: global-setting-updated.${window.adminId}`);
    } else {
        console.error('Admin ID not found. Echo cannot subscribe to private channel.');
    }
});
