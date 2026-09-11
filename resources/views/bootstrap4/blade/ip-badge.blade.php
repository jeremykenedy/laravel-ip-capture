<span {{ $attributes->merge(['class' => 'badge badge-pill '.($captured() ? 'badge-success' : 'badge-secondary')]) }}>
    @if ($label)
        <span class="font-weight-normal">{{ $label }}</span>
    @endif
    <span>{{ $value() }}</span>
</span>
