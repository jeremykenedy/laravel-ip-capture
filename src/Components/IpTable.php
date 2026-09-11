<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

class IpTable extends Component
{
    /**
     * @param array<string, mixed>|null $columns
     */
    public function __construct(
        public ?Model $model = null,
        public ?array $columns = null,
        public ?string $title = null,
        public ?bool $mask = null,
        public bool $live = false,
    ) {
    }

    /**
     * One row per configured column, whether or not it holds an address.
     *
     * @return list<array{column: string, label: string, value: string, captured: bool}>
     */
    public function rows(): array
    {
        $rows = [];

        foreach ($this->sourceValues() as $column => $value) {
            $captured = is_string($value) && $value !== '';

            $rows[] = [
                'column'   => $column,
                'label'    => IpCapture::columnLabel($column),
                'value'    => $captured ? $this->present($value) : IpCapture::emptyLabel(),
                'captured' => $captured,
            ];
        }

        return $rows;
    }

    public function heading(): string
    {
        return $this->title ?? trans(IpCapture::VIEW_NAMESPACE.'::ip-capture.table_title');
    }

    public function render(): View
    {
        return view(IpCapture::VIEW_NAMESPACE.'::ip-table');
    }

    /**
     * @return array<string, mixed>
     */
    protected function sourceValues(): array
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        $values = [];

        foreach (IpCapture::enabledColumns() as $column) {
            $values[$column] = $this->model?->getAttribute($column);
        }

        return $values;
    }

    protected function present(string $ip): string
    {
        return ($this->mask ?? IpCapture::shouldMaskDisplay()) ? IpCapture::mask($ip) : $ip;
    }
}
