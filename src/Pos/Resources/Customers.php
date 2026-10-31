<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Khách hàng (customers) trong một cửa hàng.
 *
 * - GET  /shops/{shop}/customers                     : danh sách.
 * - POST /shops/{shop}/customers                     : tạo khách.
 * - GET  /shops/{shop}/customers/{id}                : chi tiết.
 * - POST /shops/{shop}/customers/{id}/create_note    : thêm ghi chú.
 * - POST /shops/{shop}/customers/{id}/load_customer_notes : danh sách ghi chú.
 */
class Customers extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(string $shopId, array $filters = []): array
    {
        return (array) $this->client->get($this->base($shopId), $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(string $shopId, array $payload): array
    {
        return (array) $this->client->post($this->base($shopId), $payload, true);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $shopId, string $customerId): array
    {
        return (array) $this->client->get($this->base($shopId) . '/' . rawurlencode($customerId));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function createNote(string $shopId, string $customerId, array $payload): array
    {
        return (array) $this->client->post(
            $this->base($shopId) . '/' . rawurlencode($customerId) . '/create_note',
            $payload
        );
    }

    protected function base(string $shopId): string
    {
        return '/shops/' . rawurlencode($shopId) . '/customers';
    }
}
