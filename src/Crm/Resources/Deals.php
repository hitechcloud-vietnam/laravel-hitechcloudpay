<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;

/**
 * Cơ hội bán hàng (deals).
 *
 * - GET    /deals       : danh sách.
 * - POST   /deals       : tạo.
 * - GET    /deals/{id}  : chi tiết.
 * - PATCH  /deals/{id}  : cập nhật (đổi stage, giá trị...).
 * - DELETE /deals/{id}  : xoá.
 */
class Deals extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/deals', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return (array) $this->client->post('/deals', $payload, true);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/deals/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function update(string $id, array $payload): array
    {
        return (array) $this->client->patch('/deals/' . rawurlencode($id), $payload);
    }

    /**
     * @return array<string,mixed>
     */
    public function delete(string $id): array
    {
        return (array) $this->client->delete('/deals/' . rawurlencode($id));
    }
}
