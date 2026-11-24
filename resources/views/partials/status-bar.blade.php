{{-- Barra di avanzamento segmentata multicolore per visualizzare la distribuzione dei task per stato --}}
@php $total = max(1, $counts->sum()); @endphp
<div class="progress-stacked mb-2" style="height: 8px; border-radius: 6px; overflow: hidden; background-color: var(--bs-border-color);">
    @foreach (\App\Enums\TaskStatus::cases() as $status)
        @if ($counts[$status->value] > 0)
        <div class="progress" role="progressbar" aria-label="{{ $status->label() }}" aria-valuenow="{{ $counts[$status->value] }}" aria-valuemin="0" aria-valuemax="{{ $total }}" style="width: {{ $counts[$status->value] / $total * 100 }}%" title="{{ $status->label() }}: {{ $counts[$status->value] }}">
            <div class="progress-bar bg-{{ $status->color() }}"></div>
        </div>
        @endif
    @endforeach
</div>
<div class="d-flex flex-wrap gap-3 small text-body-secondary align-items-center">
    @foreach (\App\Enums\TaskStatus::cases() as $status)
        <span class="d-inline-flex align-items-center" title="{{ $status->label() }}">
            <i class="bi bi-circle-fill text-{{ $status->color() }} me-1" style="font-size: 0.5rem;"></i>
            <span class="text-body-secondary me-1">{{ $status->label() }}:</span>
            <strong class="text-body">{{ $counts[$status->value] }}</strong>
        </span>
    @endforeach
    <span class="ms-auto fw-medium text-body">{{ $counts->sum() }} totali</span>
</div>

