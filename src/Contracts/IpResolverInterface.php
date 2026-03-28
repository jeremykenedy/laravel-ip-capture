<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Contracts;

interface IpResolverInterface
{
    public function getClientIp(): string;
}
