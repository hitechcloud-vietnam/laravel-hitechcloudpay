<?php

declare(strict_types=1);

namespace HiTechCloud\Pay;

use HiTechCloud\HiTechCloud;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider hợp nhất của HiTechCloud Laravel SDK (Laravel 9–13).
 *
 * Giữ nguyên tên lớp `HiTechCloudPayServiceProvider` và namespace cũ để tương
 * thích ngược (auto-discovery không đổi), nhưng nay đăng ký TOÀN BỘ module:
 *
 * - Merge + publish config `hitechcloudpay.php` (tag `hitechcloudpay-config`).
 * - Bind singleton `HiTechCloud\Pay\HiTechCloudPay` (BC) + alias `hitechcloudpay`.
 * - Bind singleton `HiTechCloud\HiTechCloud` (manager hợp nhất) + alias `hitechcloud`.
 * - Nạp & publish migrations (tag `hitechcloud-migrations`).
 * - Nạp routes webhook (routes/hitechcloudpay.php) nếu `webhooks.enabled` bật.
 */
class HiTechCloudPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/hitechcloudpay.php', 'hitechcloudpay');

        // Client Pay (tương thích ngược).
        $this->app->singleton(HiTechCloudPay::class, function ($app) {
            /** @var array<string,mixed> $config */
            $config = $app['config']->get('hitechcloudpay', []);

            return new HiTechCloudPay($config);
        });
        $this->app->alias(HiTechCloudPay::class, 'hitechcloudpay');

        // Manager hợp nhất cho toàn bộ SDK.
        $this->app->singleton(HiTechCloud::class, function ($app) {
            /** @var array<string,mixed> $config */
            $config = $app['config']->get('hitechcloudpay', []);

            return new HiTechCloud($config);
        });
        $this->app->alias(HiTechCloud::class, 'hitechcloud');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if ((bool) config('hitechcloudpay.webhooks.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/hitechcloudpay.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/hitechcloudpay.php' => $this->app->configPath('hitechcloudpay.php'),
            ], 'hitechcloudpay-config');

            $this->publishes([
                __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
            ], 'hitechcloud-migrations');
        }
    }
}
