<?php

declare(strict_types=1);

namespace HiTechCloud\Ekyc\Resources;

use HiTechCloud\Ekyc\EkycClient;
use HiTechCloud\Support\Resource;

/**
 * Giao dịch eKYC cho một service-code cụ thể.
 *
 * - POST /{service-code}/api/agent/init       : khởi tạo giao dịch (trả QR/deeplink).
 * - POST /{service-code}/api/get-transaction  : lấy kết quả giao dịch.
 * - POST /{service-code}/v2/api/get-transaction : lấy kết quả (API v2).
 *
 * Mọi request được ký RSA qua {@see EkycClient::signedPost()}.
 */
class Session extends Resource
{
    protected string $serviceCode;

    public function __construct(EkycClient $client, string $serviceCode)
    {
        parent::__construct($client);
        $this->serviceCode = $serviceCode;
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function init(array $payload): array
    {
        return $this->ekyc()->signedPost($this->path('/api/agent/init'), $payload);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function getTransaction(array $payload): array
    {
        return $this->ekyc()->signedPost($this->path('/api/get-transaction'), $payload);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function getTransactionV2(array $payload): array
    {
        return $this->ekyc()->signedPost($this->path('/v2/api/get-transaction'), $payload);
    }

    protected function path(string $suffix): string
    {
        return '/' . rawurlencode($this->serviceCode) . $suffix;
    }

    protected function ekyc(): EkycClient
    {
        /** @var EkycClient $client */
        $client = $this->client;

        return $client;
    }
}
