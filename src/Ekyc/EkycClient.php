<?php

declare(strict_types=1);

namespace HiTechCloud\Ekyc;

use HiTechCloud\Ekyc\Resources\ShareInfo;
use HiTechCloud\Ekyc\Resources\Session;
use HiTechCloud\Ekyc\Resources\Sso;
use HiTechCloud\Support\Client;

/**
 * Client eKYC / VNeID (ekyc-c06.hitechcloud.vn) – cổng đại lý (agent gateway) C06.
 *
 * - Base URL : https://ekyc-c06.hitechcloud.vn/api/tcdn
 * - Xác thực : agent ký request bằng RSA (SHA256withRSA); payload có thể được
 *   mã hoá AES. Xem {@see Crypto}.
 * - Mỗi nghiệp vụ C06 là một "service-code" (ví dụ share-info, face-matching,
 *   create-account...) với các endpoint `/{service-code}/api/agent/init` và
 *   `/{service-code}/api/get-transaction`.
 *
 * Module này TẮT tới khi cấu hình `ekyc.*` (agent_code + RSA key) được khai báo
 * và agent đã đăng ký với C06.
 */
class EkycClient extends Client
{
    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('ekyc.base_url'), '/') . '/api/tcdn';
    }

    /**
     * Header định danh agent. Chữ ký RSA được gắn theo từng body (xem {@see signedPost()}).
     *
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        return array_filter([
            'X-Agent-Code' => (string) ($this->cfg('ekyc.agent_code') ?? ''),
        ]);
    }

    /**
     * Gửi một request đã ký RSA tới cổng C06.
     *
     * Envelope: `{ "data": <payload|aes>, "signature": <base64 RSA> }`.
     * Nếu có cấu hình `ekyc.aes_key`, payload được mã hoá AES trước khi ký.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function signedPost(string $path, array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $json = $json === false ? '{}' : $json;

        $aesKey = (string) ($this->cfg('ekyc.aes_key') ?? '');
        $data = $aesKey !== '' ? Crypto::encryptAes($json, $aesKey) : $json;

        $signature = Crypto::signRsa($data, $this->requireCfg('ekyc.private_key'));

        $response = $this->request('POST', $path, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'data' => $data,
                'signature' => $signature,
                'agent_code' => (string) ($this->cfg('ekyc.agent_code') ?? ''),
            ],
        ]);

        return is_array($response) ? $response : [];
    }

    /**
     * Giao dịch eKYC theo service-code (init / get-transaction).
     */
    public function session(string $serviceCode): Session
    {
        return new Session($this, $serviceCode);
    }

    /**
     * Chia sẻ thông tin công dân (share-info).
     */
    public function shareInfo(): ShareInfo
    {
        return $this->resource('shareInfo', ShareInfo::class);
    }

    /**
     * Đăng nhập VNeID (SSO).
     */
    public function sso(): Sso
    {
        return $this->resource('sso', Sso::class);
    }
}
