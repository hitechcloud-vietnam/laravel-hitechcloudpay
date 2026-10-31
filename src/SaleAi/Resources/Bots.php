<?php

declare(strict_types=1);

namespace HiTechCloud\SaleAi\Resources;

use HiTechCloud\Support\Resource;

/**
 * Bot bán hàng AI (bots) & tin nhắn.
 *
 * - GET  /bots                   : danh sách bot.
 * - POST /bots                   : tạo bot.
 * - GET  /bots/{bot}/messages    : lịch sử tin nhắn.
 * - POST /bots/{bot}/messages    : gửi tin nhắn (bot trả lời).
 */
class Bots extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/bots', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return (array) $this->client->post('/bots', $payload, true);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function messages(string $botId, array $filters = []): array
    {
        return (array) $this->client->get('/bots/' . rawurlencode($botId) . '/messages', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function sendMessage(string $botId, array $payload): array
    {
        return (array) $this->client->post('/bots/' . rawurlencode($botId) . '/messages', $payload, true);
    }
}
