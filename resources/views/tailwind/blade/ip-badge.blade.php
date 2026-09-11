<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium '.($captured() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400')]) }}>
    @if ($label)
        <span class="font-normal opacity-70">{{ $label }}</span>
    @endif
    <span class="font-mono">{{ $value() }}</span>
</span>
