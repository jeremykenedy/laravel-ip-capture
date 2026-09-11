<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jeremykenedy\LaravelIpCapture\Traits\CapturesIp;

class SoftDeletingIpCaptureUser extends Model
{
    use CapturesIp;
    use SoftDeletes;

    protected $table = 'users';

    protected $guarded = [];
}
