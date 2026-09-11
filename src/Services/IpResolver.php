<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Services;

use Illuminate\Http\Request;
use InvalidArgumentException;
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

        $ip = $this->resolve();

        if (IpCapture::shouldAnonymize()) {
            $ip = IpCapture::anonymize($ip);
        }

        if (IpCapture::shouldHash()) {
            return $this->hash($ip);
        }

        return $ip;
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

    protected function hash(string $ip): string
    {
        $algo = IpCapture::hashAlgo();

        if (!in_array($algo, hash_algos(), true)) {
            throw new InvalidArgumentException(
                "Unsupported hashing algorithm [{$algo}] configured in ip-capture.hash_algo."
            );
        }

        return hash($algo, IpCapture::hashSalt().$ip);
    }
}
