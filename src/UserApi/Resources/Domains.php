<?php

declare(strict_types=1);

namespace HiTechCloud\UserApi\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tên miền.
 *
 * - GET /api/domain/availability/{domain} : kiểm tra khả dụng của tên miền.
 */
class Domains extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function availability(string $domain): array
    {
        return (array) $this->client->get('/api/domain/availability/' . rawurlencode($domain));
    }
}
