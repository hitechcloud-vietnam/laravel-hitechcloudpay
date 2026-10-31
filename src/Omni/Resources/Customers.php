<?php

declare(strict_types=1);

namespace HiTechCloud\Omni\Resources;

use HiTechCloud\Support\Resource;

/**
 * Khách hàng (customers) trong Omnichannel.
 *
 * - GET  /customers                      : danh sách.
 * - GET  /customers/{id}                 : chi tiết.
 * - POST /customers/{id}/notes           : thêm ghi chú.
 */
class Customers extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/customers', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/customers/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function addNote(string $id, array $payload): array
    {
        return (array) $this->client->post('/customers/' . rawurlencode($id) . '/notes', $payload);
    }
}
