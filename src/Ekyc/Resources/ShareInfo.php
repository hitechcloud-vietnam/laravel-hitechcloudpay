<?php

declare(strict_types=1);

namespace HiTechCloud\Ekyc\Resources;

use HiTechCloud\Ekyc\EkycClient;
use HiTechCloud\Support\Resource;

/**
 * Chia sẻ thông tin công dân (share-info) qua VNeID.
 *
 * - POST /share-info/api/agent/init       : khởi tạo yêu cầu chia sẻ.
 * - POST /share-info/api/get-transaction  : lấy dữ liệu đã chia sẻ.
 */
class ShareInfo extends Resource
{
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function init(array $payload): array
    {
        return $this->ekyc()->signedPost('/share-info/api/agent/init', $payload);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function getTransaction(array $payload): array
    {
        return $this->ekyc()->signedPost('/share-info/api/get-transaction', $payload);
    }

    protected function ekyc(): EkycClient
    {
        /** @var EkycClient $client */
        $client = $this->client;

        return $client;
    }
}
