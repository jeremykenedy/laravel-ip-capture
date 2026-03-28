<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Services;

use Illuminate\Http\Request;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;

class IpResolver implements IpResolverInterface
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function getClientIp(): string
    {
        $ip = $this->resolve();

        if (config('ip-capture.hash', false)) {
            return hash(config('ip-capture.hash_algo', 'sha256'), $ip);
        }

        return $ip;
    }

    protected function resolve(): string
    {
        if (config('ip-capture.trust_proxies', true)) {
            $ip = $this->request->ip();

            if ($ip !== null && $ip !== '127.0.0.1') {
                return $ip;
            }
        }

        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            $value = $this->request->server($header);

            if ($value !== null) {
                $ips = array_map('trim', explode(',', $value));
                $filtered = filter_var($ips[0], FILTER_VALIDATE_IP);

                if ($filtered !== false) {
                    return $filtered;
                }
            }
        }

        return config('ip-capture.null_ip', '0.0.0.0');
    }
}
