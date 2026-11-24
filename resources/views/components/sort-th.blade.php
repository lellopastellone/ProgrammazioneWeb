{{-- Intestazione di tabella cliccabile per l'ordinamento dinamico delle righe lato client --}}
@props(['label', 'default' => false])

<th {{ $attributes->merge(['class' => 'text-body-secondary']) }} aria-sort="{{ $default ? 'ascending' : 'none' }}">
    <a href="#" role="button" data-sort @class(['text-decoration-none text-nowrap', 'link-body-emphasis' => $default, 'link-secondary' => ! $default])>
        {{ $label }}<i @class(['bi ms-1', 'bi-caret-up-fill' => $default, 'bi-chevron-expand opacity-50' => ! $default])></i>
    </a>
</th>
