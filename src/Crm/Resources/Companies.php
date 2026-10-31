<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;

/**
 * Công ty (companies).
 *
 * - GET    /companies       : danh sách.
 * - POST   /companies       : tạo.
 * - GET    /companies/{id}  : chi tiết.
 * - PATCH  /companies/{id}  : cập nhật.
 * - DELETE /companies/{id}  : xoá.
 */
class Companies extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/companies', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return (array) $this->client->post('/companies', $payload, true);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/companies/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function update(string $id, array $payload): array
    {
        return (array) $this->client->patch('/companies/' . rawurlencode($id), $payload);
    }

    /**
     * @return array<string,mixed>
     */
    public function delete(string $id): array
    {
        return (array) $this->client->delete('/companies/' . rawurlencode($id));
    }
}
