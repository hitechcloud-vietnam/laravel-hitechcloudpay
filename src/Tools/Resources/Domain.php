<?php

declare(strict_types=1);

namespace HiTechCloud\Tools\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tên miền: kiểm tra, WHOIS, xếp hạng.
 *
 * - GET /api/domain/check   : kiểm tra khả dụng.
 * - GET /api/domain/whois   : tra cứu WHOIS.
 * - GET /api/domain/rank    : xếp hạng tên miền.
 */
class Domain extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function check(string $domain): array
    {
        return (array) $this->client->get('/api/domain/check', ['domain' => $domain]);
    }

    /**
     * @return array<string,mixed>
     */
    public function whois(string $domain): array
    {
        return (array) $this->client->get('/api/domain/whois', ['domain' => $domain]);
    }

    /**
     * @return array<string,mixed>
     */
    public function rank(string $domain): array
    {
        return (array) $this->client->get('/api/domain/rank', ['domain' => $domain]);
    }
}
