<?php

declare(strict_types=1);

namespace HiTechCloud\Omni;

use HiTechCloud\Omni\Resources\Channels;
use HiTechCloud\Omni\Resources\Conversations;
use HiTechCloud\Omni\Resources\Customers;
use HiTechCloud\Omni\Resources\Stats;
use HiTechCloud\Omni\Resources\Webhooks;
use HiTechCloud\Support\Client;

/**
 * Client Omnichannel (omni.hitechcloud.vn).
 *
 * - Base URL : https://omni.hitechcloud.vn/v1
 * - Xác thực : Bearer access token.
 * - Webhook  : header `X-HTC-Signature` (middleware loại `omni`).
 */
class OmniClient extends Client
{
    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('omni.base_url'), '/') . '/v1';
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        return $this->bearerHeader($this->requireCfg('omni.token'));
    }

    public function channels(): Channels
    {
        return $this->resource('channels', Channels::class);
    }

    public function conversations(): Conversations
    {
        return $this->resource('conversations', Conversations::class);
    }

    public function customers(): Customers
    {
        return $this->resource('customers', Customers::class);
    }

    public function stats(): Stats
    {
        return $this->resource('stats', Stats::class);
    }

    public function webhooks(): Webhooks
    {
        return $this->resource('webhooks', Webhooks::class);
    }

    /**
     * Thông tin tài khoản/agent hiện hành (GET /v1/me).
     *
     * @return array<string,mixed>
     */
    public function me(): array
    {
        return (array) $this->get('/me');
    }
}
