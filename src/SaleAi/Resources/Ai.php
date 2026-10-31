<?php

declare(strict_types=1);

namespace HiTechCloud\SaleAi\Resources;

use HiTechCloud\Support\Resource;

/**
 * Điều phối AI (ai): bàn giao hội thoại cho nhân viên (human handover).
 *
 * - POST /ai/handover : yêu cầu/giải quyết bàn giao hội thoại.
 */
class Ai extends Resource
{
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function handover(array $payload): array
    {
        return (array) $this->client->post('/ai/handover', $payload);
    }
}
