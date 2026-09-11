<span {{ $attributes->merge(['class' => 'badge rounded-pill '.($captured() ? 'bg-success' : 'bg-secondary')]) }}>
    @if ($label)
        <span class="fw-normal opacity-75">{{ $label }}</span>
    @endif
    <span class="font-monospace">{{ $value() }}</span>
</span>
