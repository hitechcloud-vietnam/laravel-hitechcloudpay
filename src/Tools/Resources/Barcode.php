<?php

declare(strict_types=1);

namespace HiTechCloud\Tools\Resources;

use HiTechCloud\Support\Resource;

/**
 * Mã vạch / GS1 (barcode).
 *
 * - GET /api/barcode/validate      : kiểm tra hợp lệ.
 * - GET /api/barcode/gs1/parse     : bóc tách GS1.
 * - GET /api/barcode/check/digit   : tính check digit.
 */
class Barcode extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function validate(string $code): array
    {
        return (array) $this->client->get('/api/barcode/validate', ['code' => $code]);
    }

    /**
     * @return array<string,mixed>
     */
    public function parseGs1(string $data): array
    {
        return (array) $this->client->get('/api/barcode/gs1/parse', ['data' => $data]);
    }

    /**
     * @return array<string,mixed>
     */
    public function checkDigit(string $code): array
    {
        return (array) $this->client->get('/api/barcode/check/digit', ['code' => $code]);
    }
}
