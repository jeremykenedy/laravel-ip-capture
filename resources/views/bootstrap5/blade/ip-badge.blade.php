<span {{ $attributes->merge(['class' => 'badge rounded-pill '.($captured() ? 'text-success-emphasis bg-success-subtle' : 'text-secondary bg-secondary-subtle')]) }}>
    @if ($label)
        <span class="fw-normal opacity-75">{{ $label }}</span>
    @endif
    <span class="font-monospace">{{ $value() }}</span>
</span>
