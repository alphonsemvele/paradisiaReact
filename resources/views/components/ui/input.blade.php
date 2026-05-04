@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'type' => 'text'
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>
    @endif
    
    <input 
        type="{{ $type }}"
        {{ $attributes->except('class')->merge([
            'class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors ' . ($error ? 'border-red-500' : '')
        ]) }}
    >
    
    @if($hint)
        <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
    @endif
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>