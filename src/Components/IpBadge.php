<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

class IpBadge extends Component
{
    public function __construct(
        public ?string $ip = null,
        public ?string $label = null,
        public ?bool $mask = null,
    ) {
    }

    public function value(): string
    {
        if ($this->ip === null || $this->ip === '') {
            return IpCapture::emptyLabel();
        }

        return ($this->mask ?? IpCapture::shouldMaskDisplay()) ? IpCapture::mask($this->ip) : $this->ip;
    }

    public function captured(): bool
    {
        return $this->ip !== null && $this->ip !== '';
    }

    public function render(): View
    {
        return view(IpCapture::VIEW_NAMESPACE.'::ip-badge');
    }
}
