<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;

/**
 * Hoạt động (activities): cuộc gọi, cuộc hẹn, việc cần làm.
 *
 * - GET  /activities      : danh sách.
 * - POST /activities      : tạo.
 * - GET  /activities/{id} : chi tiết.
 */
class Activities extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/activities', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return (array) $this->client->post('/activities', $payload, true);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/activities/' . rawurlencode($id));
    }
}
