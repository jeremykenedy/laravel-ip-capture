<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-header d-flex align-items-center justify-content-between gap-3">
        <h3 class="h6 mb-0">{{ $heading() }}</h3>

        @if ($live)
            <button type="button" wire:click="$refresh" class="btn btn-sm btn-outline-secondary">
                {{ __('ip-capture::ip-capture.refresh') }}
            </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <caption class="visually-hidden">{{ $heading() }}</caption>
            <thead>
                <tr class="text-secondary text-uppercase small">
                    <th scope="col">{{ __('ip-capture::ip-capture.header_event') }}</th>
                    <th scope="col">{{ __('ip-capture::ip-capture.header_value') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows() as $row)
                    <tr data-column="{{ $row['column'] }}">
                        <th scope="row" class="fw-normal text-body-secondary">{{ $row['label'] }}</th>
                        <td>
                            @if ($row['captured'])
                                <code>{{ $row['value'] }}</code>
                            @else
                                <span class="text-secondary">{{ $row['value'] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-secondary">{{ __('ip-capture::ip-capture.no_records') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
