<?php

use Jeremykenedy\LaravelIpCapture\Support\IpCapture;
use Jeremykenedy\LaravelIpCapture\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

dataset('frameworks', function () {
    foreach (IpCapture::CSS_FRAMEWORKS as $css) {
        foreach (IpCapture::FRONTENDS as $frontend) {
            yield [$css, $frontend];
        }
    }
});
