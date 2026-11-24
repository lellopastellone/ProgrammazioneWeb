{{-- Layout a due colonne per sezioni di azione diretta senza campi di testo --}}
<div {{ $attributes->merge(['class' => 'row g-3']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="col-md-8">
        <div class="card shadow-sm border-0"><div class="card-body p-4">{{ $content }}</div></div>
    </div>
</div>
