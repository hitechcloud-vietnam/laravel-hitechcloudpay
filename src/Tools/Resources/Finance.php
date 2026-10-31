<?php

declare(strict_types=1);

namespace HiTechCloud\Tools\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tài chính: tỷ giá, tiền mã hoá, danh sách ngân hàng.
 *
 * - GET /api/fx/rates           : tỷ giá hiện hành.
 * - GET /api/fx/convert         : quy đổi tiền tệ.
 * - GET /api/fx/crypto          : giá tiền mã hoá.
 * - GET /api/banks              : danh sách ngân hàng VN.
 */
class Finance extends Resource
{
    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function rates(array $query = []): array
    {
        return (array) $this->client->get('/api/fx/rates', $query);
    }

    /**
     * @return array<string,mixed>
     */
    public function convert(string $from, string $to, float $amount): array
    {
        return (array) $this->client->get('/api/fx/convert', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ]);
    }

    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function crypto(array $query = []): array
    {
        return (array) $this->client->get('/api/fx/crypto', $query);
    }

    /**
     * @return array<string,mixed>
     */
    public function banks(): array
    {
        return (array) $this->client->get('/api/banks');
    }
}
