<?php

declare(strict_types=1);

namespace HiTechCloud\Omni\Resources;

use HiTechCloud\Support\Resource;

/**
 * Thống kê (stats).
 *
 * - GET /stats/channels       : hiệu suất theo kênh.
 * - GET /stats/engagements    : tương tác.
 * - GET /stats/new-customers  : khách mới.
 * - GET /stats/tags           : theo nhãn.
 */
class Stats extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function channels(array $filters = []): array
    {
        return (array) $this->client->get('/stats/channels', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function engagements(array $filters = []): array
    {
        return (array) $this->client->get('/stats/engagements', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function newCustomers(array $filters = []): array
    {
        return (array) $this->client->get('/stats/new-customers', $filters);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function tags(array $filters = []): array
    {
        return (array) $this->client->get('/stats/tags', $filters);
    }
}
