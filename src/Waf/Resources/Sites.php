<?php

declare(strict_types=1);

namespace HiTechCloud\Waf\Resources;

use HiTechCloud\Support\Resource;

/**
 * Website được bảo vệ (sites) và chống sửa đổi nội dung của site.
 *
 * - GET  /business/site                              : danh sách site.
 * - GET  /business/site/{site_id}/anti_tamper        : cấu hình chống giả mạo.
 * - GET  /business/site/{site_id}/anti_tamper/resource/{resource_id} : tài nguyên.
 */
class Sites extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/business/site', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function antiTamper(string $siteId): array
    {
        return (array) $this->client->get('/business/site/' . rawurlencode($siteId) . '/anti_tamper');
    }

    /**
     * @return array<string,mixed>
     */
    public function antiTamperResource(string $siteId, string $resourceId): array
    {
        return (array) $this->client->get(
            '/business/site/' . rawurlencode($siteId) . '/anti_tamper/resource/' . rawurlencode($resourceId)
        );
    }
}
