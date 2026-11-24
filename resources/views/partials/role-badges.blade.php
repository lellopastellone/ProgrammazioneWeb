{{-- Badge distintivi con icona e colore per visualizzare i ruoli assegnati all'utente --}}
@foreach ($user->roles as $role)
    @php
        [$badgeClass, $icon] = match($role->name) {
            'organizer' => ['text-bg-dark', 'bi-shield-fill-check'],
            'project_manager' => ['text-bg-primary', 'bi-briefcase-fill'],
            default => ['bg-light text-secondary border', 'bi-person-fill'],
        };
        $label = match($role->name) {
            'organizer' => 'Organizer',
            'project_manager' => 'Project Manager',
            'member' => 'Member',
            default => ucfirst(str_replace('_', ' ', $role->name)),
        };
    @endphp
    <span class="badge rounded-pill fw-medium d-inline-flex align-items-center gap-1 {{ $badgeClass }}" style="font-size: 0.75rem;">
        <i class="bi {{ $icon }}"></i>
        <span>{{ $label }}</span>
    </span>
@endforeach

