<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Cửa hàng (shops).
 *
 * - GET  /shops            : danh sách cửa hàng.
 * - GET  /shops/{id}       : chi tiết cửa hàng.
 * - GET  /shops/{id}/brand : thông tin thương hiệu.
 */
class Shops extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/shops', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $shopId): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId));
    }

    /**
     * @return array<string,mixed>
     */
    public function brand(string $shopId): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId) . '/brand');
    }
}
