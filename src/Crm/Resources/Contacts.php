<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;

/**
 * Liên hệ (contacts).
 *
 * - GET    /contacts            : danh sách.
 * - POST   /contacts            : tạo liên hệ.
 * - GET    /contacts/{id}       : chi tiết.
 * - PATCH  /contacts/{id}       : cập nhật.
 * - DELETE /contacts/{id}       : xoá.
 * - POST   /contacts/{id}/tags  : gắn nhãn.
 */
class Contacts extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/contacts', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return (array) $this->client->post('/contacts', $payload, true);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/contacts/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function update(string $id, array $payload): array
    {
        return (array) $this->client->patch('/contacts/' . rawurlencode($id), $payload);
    }

    /**
     * @return array<string,mixed>
     */
    public function delete(string $id): array
    {
        return (array) $this->client->delete('/contacts/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function tags(string $id, array $payload): array
    {
        return (array) $this->client->post('/contacts/' . rawurlencode($id) . '/tags', $payload);
    }
}
