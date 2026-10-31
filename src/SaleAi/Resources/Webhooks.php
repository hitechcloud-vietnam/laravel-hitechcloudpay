<?php

declare(strict_types=1);

namespace HiTechCloud\SaleAi\Resources;

use HiTechCloud\Support\Resource;
use HiTechCloud\Support\Signature;

/**
 * Quản trị & xác minh webhook Sale AI.
 *
 * - GET    /webhooks       : danh sách endpoint.
 * - POST   /webhooks       : đăng ký endpoint.
 * - DELETE /webhooks/{id}  : xoá endpoint.
 *
 * Webhook Sale AI ký HMAC-SHA256 raw body ở header `X-SaleAI-Signature`.
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
     * @return array<string,mixed>
     */
    public function delete(string $id): array
    {
        return (array) $this->client->delete('/webhooks/' . rawurlencode($id));
    }

    public function verify(string $rawBody, string $signatureHeader, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) ($this->client->config()['saleai']['webhook_secret'] ?? '');

        return Signature::verifyRawBody($rawBody, $signatureHeader, $secret);
    }
}
