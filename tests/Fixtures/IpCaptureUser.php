<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Jeremykenedy\LaravelIpCapture\Traits\CapturesIp;

class IpCaptureUser extends Model
{
    use CapturesIp;

    protected $table = 'users';

    protected $guarded = [];
}
