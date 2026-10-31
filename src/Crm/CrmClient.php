<?php

declare(strict_types=1);

namespace HiTechCloud\Crm;

use HiTechCloud\Crm\Resources\Activities;
use HiTechCloud\Crm\Resources\Companies;
use HiTechCloud\Crm\Resources\Contacts;
use HiTechCloud\Crm\Resources\Deals;
use HiTechCloud\Crm\Resources\Notes;
use HiTechCloud\Crm\Resources\Pipelines;
use HiTechCloud\Crm\Resources\Webhooks;
use HiTechCloud\Support\Client;

/**
 * Client CRM (crm-gateways.hitechcloud.vn).
 *
 * - Base URL : https://crm-gateways.hitechcloud.vn/v1
 * - Xác thực : Bearer access token tĩnh (`crm.token`) HOẶC OAuth2 client
 *   credentials (`crm.client_id` + `crm.client_secret` -> POST /v1/oauth/token).
 * - Webhook  : header `X-CRM-Signature` (middleware loại `crm`).
 */
class CrmClient extends Client
{
    protected ?string $oauthToken = null;

    protected int $oauthExpiresAt = 0;

    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('crm.base_url'), '/') . '/v1';
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        $static = (string) ($this->cfg('crm.token') ?? '');
        if ($static !== '') {
            return $this->bearerHeader($static);
        }

        return $this->bearerHeader($this->oauthToken());
    }

    /**
     * Lấy access token qua OAuth2 client credentials (có cache trong vòng đời instance).
     */
    public function oauthToken(bool $forceRefresh = false): string
    {
        $now = time();

        if (! $forceRefresh && $this->oauthToken !== null) {
            if ($this->oauthExpiresAt === 0 || $now < ($this->oauthExpiresAt - 30)) {
                return $this->oauthToken;
            }
        }

        $response = $this->http->request(
            'POST',
            $this->url('/oauth/token'),
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->requireCfg('crm.client_id'),
                    'client_secret' => $this->requireCfg('crm.client_secret'),
                ],
            ]
        );
        $response = is_array($response) ? $response : [];
        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;

        $this->oauthToken = (string) ($data['access_token'] ?? '');
        $ttl = (int) ($data['expires_in'] ?? 0);
        $this->oauthExpiresAt = $ttl > 0 ? ($now + $ttl) : 0;

        return $this->oauthToken;
    }

    public function contacts(): Contacts
    {
        return $this->resource('contacts', Contacts::class);
    }

    public function companies(): Companies
    {
        return $this->resource('companies', Companies::class);
    }

    public function deals(): Deals
    {
        return $this->resource('deals', Deals::class);
    }

    public function pipelines(): Pipelines
    {
        return $this->resource('pipelines', Pipelines::class);
    }

    public function activities(): Activities
    {
        return $this->resource('activities', Activities::class);
    }

    public function notes(): Notes
    {
        return $this->resource('notes', Notes::class);
    }

    public function webhooks(): Webhooks
    {
        return $this->resource('webhooks', Webhooks::class);
    }
}
