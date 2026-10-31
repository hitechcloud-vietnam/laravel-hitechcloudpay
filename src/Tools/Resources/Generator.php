<?php

declare(strict_types=1);

namespace HiTechCloud\Tools\Resources;

use HiTechCloud\Support\Resource;

/**
 * Bộ sinh (generator): VietQR, CSR...
 *
 * - POST /api/generator/vietqr  : sinh mã VietQR.
 * - POST /api/generator/csr     : sinh CSR chứng chỉ.
 */
class Generator extends Resource
{
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function vietqr(array $payload): array
    {
        return (array) $this->client->post('/api/generator/vietqr', $payload);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function csr(array $payload): array
    {
        return (array) $this->client->post('/api/generator/csr', $payload);
    }
}
