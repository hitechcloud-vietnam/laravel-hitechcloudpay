<?php

declare(strict_types=1);

namespace HiTechCloud\Pay;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider đăng ký SDK HiTechCloud Pay với Laravel 9–13.
 *
 * - Merge + publish file config `hitechcloudpay.php` (tag `hitechcloudpay-config`).
 * - Bind singleton client `HiTechCloudPay` từ cấu hình.
 * - Đăng ký alias `hitechcloudpay` cho facade.
 */
class HiTechCloudPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hitechcloudpay.php', 'hitechcloudpay');

        $this->app->singleton(HiTechCloudPay::class, function ($app) {
            /** @var array<string,mixed> $config */
            $config = $app['config']->get('hitechcloudpay', []);

            return new HiTechCloudPay($config);
        });

        // Alias dùng cho facade (HiTechCloudPay::...).
        $this->app->alias(HiTechCloudPay::class, 'hitechcloudpay');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/hitechcloudpay.php' => $this->app->configPath('hitechcloudpay.php'),
            ], 'hitechcloudpay-config');
        }
    }
}
