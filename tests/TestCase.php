<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Tests;

use HiTechCloud\Pay\Facades\HiTechCloudPay;
use HiTechCloud\Pay\HiTechCloudPayServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array<int,class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [HiTechCloudPayServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array<string,class-string>
     */
    protected function getPackageAliases($app): array
    {
        return ['HiTechCloudPay' => HiTechCloudPay::class];
    }
}
