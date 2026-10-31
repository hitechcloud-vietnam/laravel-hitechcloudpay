<?php

declare(strict_types=1);

namespace HiTechCloud\Pos\Resources;

use HiTechCloud\Support\Resource;

/**
 * Dữ liệu địa giới hành chính (geo) dùng khi tạo đơn / địa chỉ giao.
 *
 * - GET /geo/provinces  : tỉnh/thành.
 * - GET /geo/districts  : quận/huyện.
 * - GET /geo/communes   : phường/xã.
 */
class Geo extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function provinces(array $filters = []): array
    {
        return (array) $this->client->get('/geo/provinces', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function districts(array $filters = []): array
    {
        return (array) $this->client->get('/geo/districts', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function communes(array $filters = []): array
    {
        return (array) $this->client->get('/geo/communes', $filters);
    }
}
