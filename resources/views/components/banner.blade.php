{{-- Banner globale superiore per messaggi di avviso e notifiche flash (successo/errore) --}}
@props(['style' => session('flash.bannerStyle', 'success'), 'message' => session('flash.banner')])

<div x-data="{{ json_encode(['show' => true, 'style' => $style, 'message' => $message]) }}"
    style="display: none;"
    x-show="show && message"
    x-on:banner-message.window="style = event.detail.style; message = event.detail.message; show = true;"
    class="alert rounded-0 mb-0 border-0 shadow-sm py-2 px-3"
    :class="{ 'alert-success': style == 'success', 'alert-danger': style == 'danger', 'alert-warning': style == 'warning', 'alert-info': style == 'info', 'alert-secondary': style != 'success' && style != 'danger' && style != 'warning' && style != 'info' }">
    <div class="container d-flex align-items-center gap-2">
        <i class="bi fs-5" :class="{
            'bi-check-circle-fill text-success': style == 'success',
            'bi-exclamation-octagon-fill text-danger': style == 'danger',
            'bi-exclamation-triangle-fill text-warning': style == 'warning',
            'bi-info-circle-fill text-info': style == 'info',
            'bi-bell-fill text-secondary': style != 'success' && style != 'danger' && style != 'warning' && style != 'info'
        }"></i>
        <span class="flex-grow-1 fw-medium" x-text="message"></span>
        <button type="button" class="btn-close ms-2" aria-label="Chiudi" x-on:click="show = false"></button>
    </div>
</div>

