<?php

declare(strict_types=1);

namespace HiTechCloud\Crm\Resources;

use HiTechCloud\Support\Resource;

/**
 * Ghi chú (notes).
 *
 * - GET  /notes : danh sách.
 * - POST /notes : tạo ghi chú gắn với contact/deal/company.
 */
class Notes extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/notes', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return (array) $this->client->post('/notes', $payload);
    }
}
