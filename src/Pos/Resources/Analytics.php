<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Báo cáo & phân tích (analytics) của một cửa hàng.
 *
 * - GET /shops/{shop}/analytics/sale                 : báo cáo doanh thu.
 * - GET /shops/{shop}/analytics/get_analytic_fields  : danh sách chỉ số.
 * - GET /shops/{shop}/customer_analytics/report      : phân tích khách hàng.
 */
class Analytics extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function sale(string $shopId, array $filters = []): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId) . '/analytics/sale', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function fields(string $shopId): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId) . '/analytics/get_analytic_fields');
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function customerReport(string $shopId, array $filters = []): array
    {
        return (array) $this->client->get('/shops/' . rawurlencode($shopId) . '/customer_analytics/report', $filters);
    }
}
