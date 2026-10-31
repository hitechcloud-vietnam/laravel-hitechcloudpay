<?php

declare(strict_types=1);

namespace HiTechCloud\Tools\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tra cứu doanh nghiệp & nhà nước.
 *
 * - GET /api/dkkd/check          : kiểm tra đăng ký kinh doanh (theo MST/MSDN).
 * - GET /api/dauthau/summary     : tổng quan đấu thầu.
 * - GET /api/ecommerce/companies : doanh nghiệp TMĐT đã đăng ký.
 */
class Business extends Resource
{
    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function dkkd(array $query): array
    {
        return (array) $this->client->get('/api/dkkd/check', $query);
    }

    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function bidding(array $query = []): array
    {
        return (array) $this->client->get('/api/dauthau/summary', $query);
    }

    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function ecommerceCompanies(array $query = []): array
    {
        return (array) $this->client->get('/api/ecommerce/companies', $query);
    }
}
