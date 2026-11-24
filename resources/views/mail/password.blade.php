{{-- Mail con il link per scegliere la password: benvenuto (WelcomeNotification) e recupero (ResetPassword, AppServiceProvider). --}}
<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<span style="display: inline-block; vertical-align: middle;">
<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle; margin-right: 8px; border-radius: 7px;">
<rect width="28" height="28" rx="7" fill="#4F46E5"/>
<path d="M8 14 A6 6 0 0 1 14 8 H20" stroke="white" stroke-width="2.2" stroke-linecap="round" fill="none"/>
<polyline points="17,5 20,8 17,11" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
<path d="M20 14 A6 6 0 0 1 14 20 H8" stroke="white" stroke-width="2.2" stroke-linecap="round" fill="none"/>
<polyline points="11,23 8,20 11,17" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
</svg><span style="display: inline-block; vertical-align: middle; font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.5px;">{{ config('app.name', 'FlowDesk') }}</span>
</span>
</x-mail::header>
</x-slot:header>

# {{ $title }}

Ciao {{ $user->name }},

{{ $intro }}

<x-mail::button :url="$url">{{ $action }}</x-mail::button>

Il link scade tra {{ $expires }}. {{ $outro }}

<x-slot:subcopy>
<x-mail::subcopy>
Se il pulsante non funziona, copia questo indirizzo nel browser: <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-mail::subcopy>
</x-slot:subcopy>

<x-slot:footer>
<x-mail::footer>© {{ date('Y') }} {{ config('app.name', 'FlowDesk') }}</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
