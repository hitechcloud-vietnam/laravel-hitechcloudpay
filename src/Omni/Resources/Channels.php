<?php

declare(strict_types=1);

namespace HiTechCloud\Omni\Resources;

use HiTechCloud\Support\Resource;

/**
 * Kênh kết nối (channels): Facebook, Zalo, Instagram, Tiktok...
 *
 * - GET  /channels                       : danh sách kênh.
 * - GET  /channels/{id}/posts            : bài viết của kênh.
 * - POST /channels/{id}/round-robin      : cấu hình chia hội thoại tự động.
 * - GET  /channels/{id}/call-logs        : nhật ký cuộc gọi.
 */
class Channels extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function list(array $filters = []): array
    {
        return (array) $this->client->get('/channels', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function posts(string $channelId, array $filters = []): array
    {
        return (array) $this->client->get('/channels/' . rawurlencode($channelId) . '/posts', $filters);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function roundRobin(string $channelId, array $payload): array
    {
        return (array) $this->client->post('/channels/' . rawurlencode($channelId) . '/round-robin', $payload);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function callLogs(string $channelId, array $filters = []): array
    {
        return (array) $this->client->get('/channels/' . rawurlencode($channelId) . '/call-logs', $filters);
    }
}
