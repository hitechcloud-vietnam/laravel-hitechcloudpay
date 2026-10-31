<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;

/**
 * Quy trình bán hàng (pipelines) & giai đoạn (stages).
 *
 * - GET /pipelines             : danh sách pipeline.
 * - GET /pipelines/{id}        : chi tiết.
 * - GET /pipelines/{id}/stages : các giai đoạn.
 */
class Pipelines extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/pipelines', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/pipelines/' . rawurlencode($id));
    }

    /**
     * @return array<string,mixed>
     */
    public function stages(string $id): array
    {
        return (array) $this->client->get('/pipelines/' . rawurlencode($id) . '/stages');
    }
}
