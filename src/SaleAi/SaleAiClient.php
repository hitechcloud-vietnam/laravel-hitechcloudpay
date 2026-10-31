<?php

declare(strict_types=1);

namespace HiTechCloud\SaleAi;

use HiTechCloud\SaleAi\Resources\Ai;
use HiTechCloud\SaleAi\Resources\Bots;
use HiTechCloud\SaleAi\Resources\Settings;
use HiTechCloud\SaleAi\Resources\Webhooks;
use HiTechCloud\Support\Client;

/**
 * Client Sale AI (sale-ai.hitechcloud.vn).
 *
 * - Base URL : https://sale-ai.hitechcloud.vn/api/v1 (sandbox: sale-ai-sandbox...).
 * - Xác thực : Bearer access token + header `X-Page-Access-Token` (token trang).
 * - Webhook  : header `X-SaleAI-Signature` (middleware loại `saleai`).
 */
class SaleAiClient extends Client
{
    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('saleai.base_url'), '/') . '/api/v1';
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        $headers = $this->bearerHeader($this->requireCfg('saleai.token'));

        $pageToken = (string) ($this->cfg('saleai.page_access_token') ?? '');
        if ($pageToken !== '') {
            $headers['X-Page-Access-Token'] = $pageToken;
        }

        return $headers;
    }

    public function bots(): Bots
    {
        return $this->resource('bots', Bots::class);
    }

    public function settings(): Settings
    {
        return $this->resource('settings', Settings::class);
    }

    public function ai(): Ai
    {
        return $this->resource('ai', Ai::class);
    }

    public function webhooks(): Webhooks
    {
        return $this->resource('webhooks', Webhooks::class);
    }
}
