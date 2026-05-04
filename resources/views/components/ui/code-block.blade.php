@props([
    'language' => 'php',
    'title' => null
])

<div {{ $attributes->merge(['class' => 'rounded-lg overflow-hidden border border-gray-200']) }}>
    @if($title)
        <div class="bg-gray-800 text-gray-200 px-4 py-2 text-sm font-medium border-b border-gray-700">
            {{ $title }}
        </div>
    @endif
    <div class="bg-gray-900 text-gray-100 p-4 overflow-x-auto">
        <pre class="text-sm"><code class="language-{{ $language }}">{{ trim($slot) }}</code></pre>
    </div>
</div>