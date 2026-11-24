{{-- Logo del brand FlowDesk in formato vettoriale SVG nativo --}}
<svg {{ $attributes->merge(['width' => '28', 'height' => '28', 'viewBox' => '0 0 28 28', 'fill' => 'none', 'xmlns' => 'http://www.w3.org/2000/svg', 'aria-label' => 'FlowDesk', 'role' => 'img']) }}>
    <rect width="28" height="28" rx="7" fill="#4F46E5"/>
    {{-- Arco superiore (freccia destra→) --}}
    <path d="M8 14 A6 6 0 0 1 14 8 H20" stroke="white" stroke-width="2.2" stroke-linecap="round" fill="none"/>
    <polyline points="17,5 20,8 17,11" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    {{-- Arco inferiore (freccia ←sinistra) --}}
    <path d="M20 14 A6 6 0 0 1 14 20 H8" stroke="white" stroke-width="2.2" stroke-linecap="round" fill="none"/>
    <polyline points="11,23 8,20 11,17" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
</svg>
