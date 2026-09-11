<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900']) }}>
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $heading() }}</h3>

        @if ($live)
            <button type="button" wire:click="$refresh" class="rounded-md px-2 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100">
                {{ __('ip-capture::ip-capture.refresh') }}
            </button>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700">
            <caption class="sr-only">{{ $heading() }}</caption>
            <thead>
                <tr class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th scope="col" class="px-4 py-2 font-medium">{{ __('ip-capture::ip-capture.header_event') }}</th>
                    <th scope="col" class="px-4 py-2 font-medium">{{ __('ip-capture::ip-capture.header_value') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows() as $row)
                    <tr data-column="{{ $row['column'] }}">
                        <th scope="row" class="whitespace-nowrap px-4 py-2 font-normal text-gray-600 dark:text-gray-300">{{ $row['label'] }}</th>
                        <td class="px-4 py-2">
                            @if ($row['captured'])
                                <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-100">{{ $row['value'] }}</code>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">{{ $row['value'] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ __('ip-capture::ip-capture.no_records') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
