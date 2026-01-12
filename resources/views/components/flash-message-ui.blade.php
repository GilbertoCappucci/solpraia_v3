<div
    x-data="{ show: false, message: '' }"
    x-on:flash.window="
        message = $event.detail.message;
        show = true;
        setTimeout(() => show = false, 4000);
    "
    x-show="show"
    x-transition
    class="fixed top-4 right-4 bg-yellow-100 text-yellow-900 px-4 py-2 rounded shadow"
>
    <span x-text="message"></span>
</div>

