{{-- Badge colorato per la visualizzazione visiva dello stato o della priorità di un task --}}
@props(['value', 'pill' => true])

<span {{ $attributes->class(['badge fw-medium', 'rounded-pill' => $pill, 'text-bg-' . $value->color()]) }}>
    @if ($pill)<i class="bi bi-circle-fill me-1" style="font-size:.45em;vertical-align:middle;"></i>@endif{{ $value->label() }}
</span>

