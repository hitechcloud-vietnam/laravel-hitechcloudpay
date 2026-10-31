<?php

declare(strict_types=1);

namespace HiTechCloud\Omni\Resources;

use HiTechCloud\Support\Resource;

/**
 * Hội thoại (conversations) & tin nhắn.
 *
 * - GET  /conversations                       : danh sách hội thoại.
 * - GET  /conversations/{id}                  : chi tiết.
 * - GET  /conversations/{id}/messages         : tin nhắn trong hội thoại.
 * - POST /conversations/{id}/messages         : gửi tin nhắn.
 * - POST /conversations/{id}/assign           : gán agent.
 * - POST /conversations/{id}/read | /unread   : đánh dấu đã/chưa đọc.
 * - POST /conversations/{id}/tags             : gắn nhãn.
 */
class Conversations extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/conversations', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        return (array) $this->client->get('/conversations/' . rawurlencode($id));
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function messages(string $id, array $filters = []): array
    {
        return (array) $this->client->get('/conversations/' . rawurlencode($id) . '/messages', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function sendMessage(string $id, array $payload): array
    {
        return (array) $this->client->post('/conversations/' . rawurlencode($id) . '/messages', $payload, true);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function assign(string $id, array $payload): array
    {
        return (array) $this->client->post('/conversations/' . rawurlencode($id) . '/assign', $payload);
    }

    /**
     * @return array<string,mixed>
     */
    public function markRead(string $id): array
    {
        return (array) $this->client->post('/conversations/' . rawurlencode($id) . '/read');
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function tags(string $id, array $payload): array
    {
        return (array) $this->client->post('/conversations/' . rawurlencode($id) . '/tags', $payload);
    }
}
