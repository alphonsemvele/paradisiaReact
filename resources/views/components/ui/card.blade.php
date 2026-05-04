@props([
    'padding' => true,
    'hover' => false
])

@php
$classes = 'bg-white rounded-lg shadow-sm border border-gray-200';
if ($padding) $classes .= ' p-6';
if ($hover) $classes .= ' transition-shadow hover:shadow-md';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>