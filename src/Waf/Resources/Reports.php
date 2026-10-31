<?php

declare(strict_types=1);

namespace HiTechCloud\Waf\Resources;

use HiTechCloud\Support\Resource;

/**
 * Báo cáo bảo mật (reports).
 *
 * - GET /business/report       : danh sách báo cáo.
 * - GET /business/report/{id}  : chi tiết báo cáo.
 */
class Reports extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/business/report', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/business/report/' . rawurlencode($id));
    }
}
