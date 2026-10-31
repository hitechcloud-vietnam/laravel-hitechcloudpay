<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Sản phẩm (products) trong một cửa hàng.
 *
 * - GET    /shops/{shop}/products        : danh sách sản phẩm.
 * - POST   /shops/{shop}/products        : tạo sản phẩm.
 * - GET    /shops/{shop}/products/{id}   : chi tiết sản phẩm.
 * - PUT    /shops/{shop}/products/{id}   : cập nhật sản phẩm.
 * - GET    /shops/{shop}/categories      : danh mục.
 */
class Products extends Resource
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
    public function get(string $shopId, string $productId): array
    {
        return (array) $this->client->get($this->base($shopId) . '/' . rawurlencode($productId));
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function update(string $shopId, string $productId, array $payload): array
    {
        return (array) $this->client->put($this->base($shopId) . '/' . rawurlencode($productId), $payload);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function categories(string $shopId, array $filters = []): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId) . '/categories', $filters);
    }

    protected function base(string $shopId): string
    {
        return '/shops/' . rawurlencode($shopId) . '/products';
    }
}
