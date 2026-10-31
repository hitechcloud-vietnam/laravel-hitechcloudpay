<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tồn kho (inventory) trong một cửa hàng.
 *
 * - POST /shops/{shop}/inventory/adjust          : điều chỉnh tồn kho.
 * - GET  /shops/{shop}/inventory_histories       : lịch sử xuất/nhập.
 * - GET  /shops/{shop}/inventory_analytics/inventory_by_product : tồn theo sản phẩm.
 */
class Inventory extends Resource
{
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function adjust(string $shopId, array $payload): array
    {
        return (array) $this->client->post(
            '/shops/' . rawurlencode($shopId) . '/inventory/adjust',
            $payload,
            true
        );
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function histories(string $shopId, array $filters = []): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId) . '/inventory_histories', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function byProduct(string $shopId, array $filters = []): array
    {
        return (array) $this->client->get(
            '/shops/' . rawurlencode($shopId) . '/inventory_analytics/inventory_by_product',
            $filters
        );
    }
}
