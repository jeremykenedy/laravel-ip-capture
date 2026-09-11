<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Services;

use Illuminate\Http\Request;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

class IpResolver implements IpResolverInterface
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function getClientIp(): string
    {
        if (!IpCapture::enabled()) {
            return IpCapture::nullIp();
        }

        return IpCapture::prepare($this->resolve());
    }

    protected function resolve(): string
    {
        if (IpCapture::trustProxies()) {
            $ip = $this->request->ip();

            if ($ip !== null && $ip !== '127.0.0.1') {
                return $ip;
            }
        }

        foreach (IpCapture::headers() as $header) {
            $ip = $this->firstValidIp($this->request->server($header));

            if ($ip !== null) {
                return $ip;
            }
        }

        return IpCapture::nullIp();
    }

    /**
     * Read the client address out of a header that may hold a proxy chain.
     */
    protected function firstValidIp(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $candidate = filter_var(trim(explode(',', $value)[0]), FILTER_VALIDATE_IP);

        return $candidate === false ? null : $candidate;
    }
}
