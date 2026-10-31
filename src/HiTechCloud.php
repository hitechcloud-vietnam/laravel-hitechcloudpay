<?php

declare(strict_types=1);

namespace HiTechCloud;

use HiTechCloud\Crm\CrmClient;
use HiTechCloud\Ekyc\EkycClient;
use HiTechCloud\Http\HttpClient;
use HiTechCloud\Omni\OmniClient;
use HiTechCloud\Pay\HiTechCloudPay;
use HiTechCloud\Pos\PosClient;
use HiTechCloud\SaleAi\SaleAiClient;
use HiTechCloud\Tools\ToolsClient;
use HiTechCloud\UserApi\UserApiClient;
use HiTechCloud\Waf\WafClient;

/**
 * Điểm vào hợp nhất của HiTechCloud Laravel SDK.
 *
 * Cung cấp accessor cho từng sản phẩm: Pay, eKYC, POS, Omni, CRM, WAF, Sale AI,
 * Tools, User API. Mỗi client được khởi tạo lười và cache trong vòng đời instance.
 *
 * Lưu ý: toàn bộ cấu hình `hitechcloudpay` được truyền vào từng client, nhờ đó
 * các client dùng chung lược đồ cấu hình dạng "dot" (ví dụ `pos.base_url`).
 */
class HiTechCloud
{
    /** @var array<string,mixed> */
    protected array $config;

    /** @var array<string,object> */
    protected array $clients = [];

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Module thanh toán HiTechCloud Pay (cổng thanh toán, Pay API v2, BankHub...).
     */
    public function pay(): HiTechCloudPay
    {
        if (! isset($this->clients['pay'])) {
            $this->clients['pay'] = new HiTechCloudPay($this->config);
        }

        return $this->clients['pay'];
    }

    public function ekyc(): EkycClient
    {
        return $this->client('ekyc', EkycClient::class);
    }

    public function pos(): PosClient
    {
        return $this->client('pos', PosClient::class);
    }

    public function omni(): OmniClient
    {
        return $this->client('omni', OmniClient::class);
    }

    public function crm(): CrmClient
    {
        return $this->client('crm', CrmClient::class);
    }

    public function waf(): WafClient
    {
        return $this->client('waf', WafClient::class);
    }

    public function saleAi(): SaleAiClient
    {
        return $this->client('saleAi', SaleAiClient::class);
    }

    public function tools(): ToolsClient
    {
        return $this->client('tools', ToolsClient::class);
    }

    public function userApi(): UserApiClient
    {
        return $this->client('userApi', UserApiClient::class);
    }

    /**
     * @return array<string,mixed>
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Khởi tạo lười và cache một client module (ký constructor (array, ?HttpClient)).
     *
     * @template T of \HiTechCloud\Support\Client
     * @param class-string<T> $class
     * @return T
     */
    protected function client(string $key, string $class)
    {
        if (! isset($this->clients[$key])) {
            $this->clients[$key] = new $class($this->config, new HttpClient(
                null,
                (int) ($this->config['timeout'] ?? 15),
                (int) ($this->config['retries'] ?? 2)
            ));
        }

        return $this->clients[$key];
    }
}
