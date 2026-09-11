<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="h6 mb-0">{{ $heading() }}</h3>

        @if ($live)
            <button type="button" wire:click="$refresh" class="btn btn-sm btn-outline-secondary">
                {{ __('ip-capture::ip-capture.refresh') }}
            </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <caption class="sr-only">{{ $heading() }}</caption>
            <thead>
                <tr class="text-muted text-uppercase small">
                    <th scope="col">{{ __('ip-capture::ip-capture.header_event') }}</th>
                    <th scope="col">{{ __('ip-capture::ip-capture.header_value') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows() as $row)
                    <tr data-column="{{ $row['column'] }}">
                        <th scope="row" class="font-weight-normal text-muted">{{ $row['label'] }}</th>
                        <td>
                            @if ($row['captured'])
                                <code>{{ $row['value'] }}</code>
                            @else
                                <span class="text-muted">{{ $row['value'] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-muted">{{ __('ip-capture::ip-capture.no_records') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
