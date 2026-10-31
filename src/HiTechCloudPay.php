<?php

declare(strict_types=1);

namespace HiTechCloud\Pay;

use HiTechCloud\Pay\Client\HttpClient;
use HiTechCloud\Pay\Resources\BankAccounts;
use HiTechCloud\Pay\Resources\BankHub;
use HiTechCloud\Pay\Resources\Balance;
use HiTechCloud\Pay\Resources\EInvoice;
use HiTechCloud\Pay\Resources\Gateway;
use HiTechCloud\Pay\Resources\Speaker;
use HiTechCloud\Pay\Resources\Transactions;
use HiTechCloud\Pay\Resources\VirtualAccounts;
use HiTechCloud\Pay\Resources\Webhooks;

/**
 * Điểm vào chính của SDK HiTechCloud Pay.
 *
 * Cung cấp các accessor cho từng nhóm sản phẩm. Mỗi resource được khởi tạo lười
 * (lazy) và cache lại trong vòng đời instance (nhờ đó token của BankHub/Speaker/
 * eInvoice được tái sử dụng).
 */
class HiTechCloudPay
{
    /** @var array<string,mixed> */
    protected array $config;

    protected HttpClient $http;

    /** @var array<string,object> */
    protected array $resources = [];

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config, ?HttpClient $http = null)
    {
        $this->config = $config;
        $this->http = $http ?: new HttpClient(
            null,
            (int) ($config['timeout'] ?? 15),
            (int) ($config['retries'] ?? 2)
        );
    }

    /**
     * Cổng thanh toán: tạo phiên thanh toán, tra cứu, hoàn tiền, đối soát.
     */
    public function gateway(): Gateway
    {
        return $this->resource('gateway', Gateway::class);
    }

    /**
     * Pay API v2: tài khoản ngân hàng.
     */
    public function bankAccounts(): BankAccounts
    {
        return $this->resource('bankAccounts', BankAccounts::class);
    }

    /**
     * Pay API v2: tài khoản ảo (Virtual Account).
     */
    public function virtualAccounts(): VirtualAccounts
    {
        return $this->resource('virtualAccounts', VirtualAccounts::class);
    }

    /**
     * Pay API v2: giao dịch biến động số dư.
     */
    public function transactions(): Transactions
    {
        return $this->resource('transactions', Transactions::class);
    }

    /**
     * Số dư tức thời (qua BankHub Open Banking).
     */
    public function balance(): Balance
    {
        return $this->resource('balance', Balance::class);
    }

    /**
     * Loa thông báo SoundBox.
     */
    public function speaker(): Speaker
    {
        return $this->resource('speaker', Speaker::class);
    }

    /**
     * Hoá đơn điện tử.
     */
    public function eInvoice(): EInvoice
    {
        return $this->resource('eInvoice', EInvoice::class);
    }

    /**
     * BankHub (Open Banking): hosted link, consent, tài khoản, sao kê.
     */
    public function bankHub(): BankHub
    {
        return $this->resource('bankHub', BankHub::class);
    }

    /**
     * Quản trị & xác minh webhook.
     */
    public function webhooks(): Webhooks
    {
        return $this->resource('webhooks', Webhooks::class);
    }

    /**
     * Lớp HttpClient bên dưới (hữu ích khi test hoặc tuỳ biến).
     */
    public function http(): HttpClient
    {
        return $this->http;
    }

    /**
     * @return array<string,mixed>
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Khởi tạo lười và cache một resource.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    protected function resource(string $key, string $class)
    {
        if (! isset($this->resources[$key])) {
            $this->resources[$key] = new $class($this->http, $this->config);
        }

        return $this->resources[$key];
    }
}
