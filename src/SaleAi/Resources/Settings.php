<?php

declare(strict_types=1);

namespace HiTechCloud\SaleAi\Resources;

use HiTechCloud\Support\Resource;

/**
 * Cấu hình Sale AI (settings).
 *
 * - GET /settings : lấy cấu hình hiện hành.
 * - PUT /settings : cập nhật cấu hình.
 */
class Settings extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function get(): array
    {
        return (array) $this->client->get('/settings');
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function update(array $payload): array
    {
        return (array) $this->client->put('/settings', $payload);
    }
}
