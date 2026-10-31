<?php

declare(strict_types=1);

namespace HiTechCloud\Ekyc\Resources;

use HiTechCloud\Ekyc\EkycClient;
use HiTechCloud\Support\Resource;

/**
 * Đăng nhập VNeID (SSO).
 *
 * - POST /sso-token/api/vneid/form/sso : tạo form/URL đăng nhập VNeID.
 * - POST /sso-userinfo/api/vneid/sso   : đổi mã lấy thông tin người dùng.
 */
class Sso extends Resource
{
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function formSso(array $payload): array
    {
        return $this->ekyc()->signedPost('/sso-token/api/vneid/form/sso', $payload);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function userInfo(array $payload): array
    {
        return $this->ekyc()->signedPost('/sso-userinfo/api/vneid/sso', $payload);
    }

    protected function ekyc(): EkycClient
    {
        /** @var EkycClient $client */
        $client = $this->client;

        return $client;
    }
}
