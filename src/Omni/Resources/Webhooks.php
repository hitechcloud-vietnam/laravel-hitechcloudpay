<?php

declare(strict_types=1);

namespace HiTechCloud\Omni\Resources;

use HiTechCloud\Support\Resource;
use HiTechCloud\Support\Signature;

/**
 * Quản trị & xác minh webhook Omnichannel.
 *
 * - GET  /webhooks    : danh sách endpoint đã đăng ký.
 * - POST /webhooks    : đăng ký endpoint nhận webhook.
 *
 * Webhook Omni ký HMAC-SHA256 raw body ở header `X-HTC-Signature`.
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

    /**
     * Xác minh chữ ký webhook Omni (header X-HTC-Signature).
     */
    public function verify(string $rawBody, string $signatureHeader, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) ($this->client->config()['omni']['webhook_secret'] ?? '');

        return Signature::verifyRawBody($rawBody, $signatureHeader, $secret);
    }
}
