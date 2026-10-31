<?php

declare(strict_types=1);

namespace HiTechCloud\Tools;

use HiTechCloud\Tools\Resources\Barcode;
use HiTechCloud\Tools\Resources\Business;
use HiTechCloud\Tools\Resources\Domain;
use HiTechCloud\Tools\Resources\Finance;
use HiTechCloud\Tools\Resources\GeoIp;
use HiTechCloud\Tools\Resources\Generator;
use HiTechCloud\Support\Client;

/**
 * Client API Tools (api-tools.hitechcloud.vn).
 *
 * - Base URL : https://api-tools.hitechcloud.vn
 * - Xác thực : header `X-API-Key` (hoặc Authorization: Bearer).
 * - Mọi endpoint trả JSON `{ success, data }`.
 *
 * Ngoài các resource nhóm sẵn, có thể gọi trực tiếp một tool bất kỳ qua
 * {@see call()} (ví dụ `/api/health/drugs`, `/api/gov/documents`).
 */
class ToolsClient extends Client
{
    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('tools.base_url'), '/');
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        return ['X-API-Key' => $this->requireCfg('tools.api_key')];
    }

    /**
     * Gọi trực tiếp một tool bất kỳ (GET).
     *
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function call(string $path, array $query = []): array
    {
        return (array) $this->get($path, $query);
    }

    /**
     * Gọi trực tiếp một tool bất kỳ (POST).
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function callPost(string $path, array $payload = []): array
    {
        return (array) $this->post($path, $payload);
    }

    public function geoIp(): GeoIp
    {
        return $this->resource('geoIp', GeoIp::class);
    }

    public function finance(): Finance
    {
        return $this->resource('finance', Finance::class);
    }

    public function domain(): Domain
    {
        return $this->resource('domain', Domain::class);
    }

    public function barcode(): Barcode
    {
        return $this->resource('barcode', Barcode::class);
    }

    public function generator(): Generator
    {
        return $this->resource('generator', Generator::class);
    }

    public function business(): Business
    {
        return $this->resource('business', Business::class);
    }
}
