<?php

declare(strict_types=1);

namespace HiTechCloud\Waf\Resources;

use HiTechCloud\Support\Resource;

/**
 * Chứng chỉ SSL (certificates).
 *
 * - GET    /open/cert       : danh sách chứng chỉ.
 * - POST   /open/cert       : tải lên chứng chỉ.
 * - GET    /open/cert/{id}  : chi tiết.
 * - DELETE /open/cert/{id}  : xoá.
 */
class Certificates extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/open/cert', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function upload(array $payload): array
    {
        return (array) $this->client->post('/open/cert', $payload);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/open/cert/' . rawurlencode($id));
    }

    /**
     * @return array<string,mixed>
     */
    public function delete(string $id): array
    {
        return (array) $this->client->delete('/open/cert/' . rawurlencode($id));
    }
}
