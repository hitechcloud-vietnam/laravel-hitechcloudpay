<?php

declare(strict_types=1);

namespace HiTechCloud\Waf\Resources;

use HiTechCloud\Support\Resource;

/**
 * Chống giả mạo nội dung (anti_tamper) cấp doanh nghiệp.
 *
 * - GET  /business/anti_tamper/{id}        : cấu hình chống giả mạo.
 * - GET  /business/anti_tamper/{id}/page   : danh sách trang được bảo vệ.
 */
class AntiTamper extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/business/anti_tamper/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function pages(string $id, array $filters = []): array
    {
        return (array) $this->client->get('/business/anti_tamper/' . rawurlencode($id) . '/page', $filters);
    }
}
