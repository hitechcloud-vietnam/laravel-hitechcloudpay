<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;
use HiTechCloud\Support\Signature;

/**
 * Quản trị & xác minh webhook CRM.
 *
 * - GET  /webhooks : danh sách endpoint.
 * - POST /webhooks : đăng ký endpoint.
 *
 * Webhook CRM ký HMAC-SHA256 raw body ở header `X-CRM-Signature`.
 */
class Webhooks extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/webhooks', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function register(array $payload): array
    {
        return (array) $this->client->post('/webhooks', $payload);
    }

    public function verify(string $rawBody, string $signatureHeader, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) ($this->client->config()['crm']['webhook_secret'] ?? '');

        return Signature::verifyRawBody($rawBody, $signatureHeader, $secret);
    }
}
