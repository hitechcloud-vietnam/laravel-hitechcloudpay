<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Đơn hàng (orders) trong một cửa hàng.
 *
 * - GET  /shops/{shop}/orders               : danh sách đơn.
 * - POST /shops/{shop}/orders               : tạo đơn.
 * - GET  /shops/{shop}/orders/{id}          : chi tiết đơn.
 * - POST /shops/{shop}/orders/{id}/status   : cập nhật trạng thái đơn.
 * - POST /shops/{shop}/orders/arrange_shipment : tạo vận đơn.
 * - GET  /shops/{shop}/orders/{id}/print    : phiếu in.
 */
class Orders extends Resource
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
    public function get(string $shopId, string $orderId): array
    {
        return (array) $this->client->get($this->base($shopId) . '/' . rawurlencode($orderId));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function updateStatus(string $shopId, string $orderId, array $payload): array
    {
        return (array) $this->client->post($this->base($shopId) . '/' . rawurlencode($orderId) . '/status', $payload);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function arrangeShipment(string $shopId, array $payload): array
    {
        return (array) $this->client->post($this->base($shopId) . '/arrange_shipment', $payload, true);
    }

    protected function base(string $shopId): string
    {
        return '/shops/' . rawurlencode($shopId) . '/orders';
    }
}
