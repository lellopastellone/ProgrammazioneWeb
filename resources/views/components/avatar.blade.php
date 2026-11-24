{{-- Avatar circolare per la foto profilo dell'utente o badge con iniziali --}}
@props(['user', 'size' => 'md'])

@php
    $colors = ['primary', 'accent', 'success', 'warning', 'secondary'];
    $color  = $colors[$user->id % 5];
    $sizeClasses = match($size) {
        'sm' => 'p-1 fs-7',
        'lg' => 'p-3 fs-5',
        default => 'p-2',
    };
    $imgDimensions = match($size) {
        'sm' => 'width: 24px; height: 24px;',
        'lg' => 'width: 64px; height: 64px;',
        default => 'width: 36px; height: 36px;',
    };
@endphp

@if ($user->profile_photo_path)
    <img src="{{ $user->profile_photo_url }}"
         alt="{{ $user->full_name }}"
         title="{{ $user->full_name }}"
         data-bs-toggle="tooltip"
         data-bs-placement="top"
         style="{{ $imgDimensions }}"
         {{ $attributes->class([
             'rounded-circle object-fit-cover',
             'opacity-50' => $user->active === false,
         ]) }}>
@else
    {{-- Iniziali in un badge tondo, colore stabile per utente; opaco se disattivato. --}}
    <span {{ $attributes->class([
        'badge rounded-pill fw-semibold',
        "text-bg-{$color}",
        $sizeClasses,
        'opacity-50' => $user->active === false,
    ]) }} title="{{ $user->full_name }}" data-bs-toggle="tooltip" data-bs-placement="top">{{ mb_substr($user->name, 0, 1) }}{{ mb_substr($user->surname, 0, 1) }}</span>
@endif

