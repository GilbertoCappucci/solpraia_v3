document.addEventListener('DOMContentLoaded', () => {

    console.log('🔥 JS DO FILAMENT CARREGOU');
    console.log(window.userId);
    console.log(window.Echo);
    
    if (!window.userId || !window.Echo) return;

    console.log('✅ Filament Reverb carregado');

    window.Echo.private(`global-setting-updated.${window.userId}`)
        .listen('.global.setting.updated', (e) => {
            console.log('📡 Broadcast recebido no Filament', e);

            Livewire.dispatch('global.setting.updated', e.globalSettingUpdated);
        });
});

