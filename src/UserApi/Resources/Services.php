<?php

declare(strict_types=1);

namespace HiTechCloud\UserApi\Resources;

use HiTechCloud\Support\Resource;

/**
 * Dịch vụ của khách hàng (VPS, hosting...).
 *
 * - GET  /api/service             : danh sách dịch vụ.
 * - GET  /api/service/{id}        : chi tiết dịch vụ.
 * - POST /api/service/{id}/reboot : khởi động lại dịch vụ.
 */
class Services extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/api/service', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/api/service/' . rawurlencode($id));
    }

    /**
     * @return array<string,mixed>
     */
    public function reboot(string $id): array
    {
        return (array) $this->client->post('/api/service/' . rawurlencode($id) . '/reboot');
    }
}
