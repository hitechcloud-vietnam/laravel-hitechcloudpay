<?php

declare(strict_types=1);

namespace HiTechCloud\UserApi\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tài khoản khách hàng.
 *
 * - GET /api/balance : số dư tài khoản.
 * - GET /api/details : thông tin chi tiết tài khoản.
 */
class Account extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function balance(): array
    {
        return (array) $this->client->get('/api/balance');
    }

    /**
     * @return array<string,mixed>
     */
    public function details(): array
    {
        return (array) $this->client->get('/api/details');
    }
}
