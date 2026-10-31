<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Tests;

use HiTechCloud\Pay\Exceptions\ConfigurationException;
use HiTechCloud\Pay\Facades\HiTechCloudPay as HiTechCloudPayFacade;
use HiTechCloud\Pay\HiTechCloudPay;
use HiTechCloud\Pay\Resources\BankAccounts;
use HiTechCloud\Pay\Resources\Gateway;
use HiTechCloud\Pay\Resources\Transactions;

class ClientConfigTest extends TestCase
{
    public function test_singleton_is_bound_and_resolvable(): void
    {
        $client = $this->app->make(HiTechCloudPay::class);

        $this->assertInstanceOf(HiTechCloudPay::class, $client);
        // Singleton: cùng một instance.
        $this->assertSame($client, $this->app->make('hitechcloudpay'));
    }

    public function test_facade_resolves_resources(): void
    {
        $this->assertInstanceOf(Gateway::class, HiTechCloudPayFacade::gateway());
        $this->assertInstanceOf(BankAccounts::class, HiTechCloudPayFacade::bankAccounts());
        $this->assertInstanceOf(Transactions::class, HiTechCloudPayFacade::transactions());
    }

    public function test_default_config_is_merged(): void
    {
        $this->assertSame('https://pay.hitechcloud.vn', config('hitechcloudpay.base_url'));
        $this->assertSame('https://userapi.hitechcloud.vn', config('hitechcloudpay.core.base_url'));
        $this->assertSame('https://pgapi.hitechcloud.vn', config('hitechcloudpay.gateway.api_url'));
    }

    public function test_resources_are_memoized(): void
    {
        $client = $this->app->make(HiTechCloudPay::class);

        $this->assertSame($client->gateway(), $client->gateway());
    }

    public function test_missing_required_config_throws(): void
    {
        // Không cấu hình merchant_id -> ném ConfigurationException khi gọi API.
        config()->set('hitechcloudpay.gateway.merchant_id', null);
        config()->set('hitechcloudpay.gateway.secret_key', null);

        $client = new HiTechCloudPay(config('hitechcloudpay'));

        $this->expectException(ConfigurationException::class);
        $client->gateway()->getOrder('INV-123');
    }
}
