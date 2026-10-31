<?php

declare(strict_types=1);

namespace HiTechCloud\Tools\Resources;

use HiTechCloud\Support\Resource;

/**
 * Tra cứu địa chỉ IP (GeoIP).
 *
 * - GET  /api/geoip/lookup?ip=...  : tra cứu một IP.
 * - GET  /api/geoip/self           : IP của client gọi.
 * - POST /api/geoip/batch          : tra cứu hàng loạt.
 */
class GeoIp extends Resource
{
    /**
     * @return array<string,mixed>
     */
    public function lookup(string $ip): array
    {
        return (array) $this->client->get('/api/geoip/lookup', ['ip' => $ip]);
    }

    /**
     * @return array<string,mixed>
     */
    public function self(): array
    {
        return (array) $this->client->get('/api/geoip/self');
    }

    /**
     * @param array<int,string> $ips
     * @return array<string,mixed>
     */
    public function batch(array $ips): array
    {
        return (array) $this->client->post('/api/geoip/batch', ['ips' => $ips]);
    }
}
