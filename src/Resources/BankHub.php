<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * BankHub - Open Banking.
 *
 * Host: bankhub-api.hitechcloud.vn.
 * Luồng: đổi client_id/client_secret lấy access token (POST /v1/token, Basic Auth),
 * sau đó dùng Bearer token cho các lời gọi còn lại.
 *
 * - POST   /v1/token                               : lấy access token.
 * - POST   /v1/link-token/create                   : tạo Hosted Link (consent).
 * - GET    /v1/bank-accounts                        : danh sách tài khoản đã liên kết.
 * - GET    /v1/bank-accounts/{id}/balance           : số dư tức thời.
 * - GET    /v1/bank-accounts/{id}/transactions      : sao kê giao dịch.
 * - DELETE /v1/bank-accounts/{id}                    : huỷ liên kết (thu hồi consent).
 * - GET    /v1/banks                                : danh sách ngân hàng hỗ trợ.
 */
class BankHub extends TokenAwareResource
{
    protected function tokenUrl(): string
    {
        return $this->url($this->requireCfg('bankhub.base_url'), '/v1/token');
    }

    protected function tokenCredentials(): array
    {
        return [
            $this->requireCfg('bankhub.client_id'),
            $this->requireCfg('bankhub.client_secret'),
        ];
    }

    protected function tokenContentType(): string
    {
        // Tài liệu minh hoạ gọi /v1/token với Content-Type form-urlencoded.
        return 'application/x-www-form-urlencoded';
    }

    protected function parseTokenResponse(array $response): array
    {
        $token = (string) ($response['access_token'] ?? '');
        $ttl = (int) ($response['ttl'] ?? 0);

        return [$token, $ttl];
    }

    /**
     * Lấy access token kèm metadata thô {code, access_token, ttl}.
     *
     * @return array<string,mixed>
     */
    public function token(): array
    {
        $this->accessToken(true);

        return [
            'access_token' => $this->accessToken,
            'ttl' => $this->tokenExpiresAt > 0 ? max(0, $this->tokenExpiresAt - time()) : 0,
        ];
    }

    /**
     * Tạo Link Token & Hosted Link URL để khách hàng liên kết ngân hàng (consent).
     *
     * Payload (theo tài liệu): company_xid, purpose (LINK_BANK_ACCOUNT),
     * user_reference, completion_redirect_uri. company_xid lấy từ config nếu
     * bạn không truyền.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed> Gồm hosted_link_url, link_token, expires_at...
     */
    public function createLinkToken(array $payload = []): array
    {
        if (! isset($payload['company_xid']) && $this->cfg('bankhub.company_xid')) {
            $payload['company_xid'] = $this->cfg('bankhub.company_xid');
        }
        $payload = array_merge(['purpose' => 'LINK_BANK_ACCOUNT'], $payload);

        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('bankhub.base_url'), '/v1/link-token/create'),
            [
                'headers' => $this->authHeaders() + ['Content-Type' => 'application/json'],
                'json' => $payload,
                'idempotent' => true,
            ]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Danh sách tài khoản ngân hàng đã liên kết qua BankHub.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function listBankAccounts(array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('bankhub.base_url'), '/v1/bank-accounts'),
            ['headers' => $this->authHeaders(), 'query' => $filters]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Tra cứu số dư khả dụng tức thời của một tài khoản.
     *
     * GET /v1/bank-accounts/{id}/balance
     *
     * @return array<string,mixed>
     */
    public function balance(string $accountId): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('bankhub.base_url'), '/v1/bank-accounts/' . rawurlencode($accountId) . '/balance'),
            ['headers' => $this->authHeaders()]
        );

        return (array) $this->data($response);
    }

    /**
     * Sao kê giao dịch của một tài khoản đã liên kết.
     *
     * GET /v1/bank-accounts/{id}/transactions
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function transactions(string $accountId, array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('bankhub.base_url'), '/v1/bank-accounts/' . rawurlencode($accountId) . '/transactions'),
            ['headers' => $this->authHeaders(), 'query' => $filters]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Huỷ liên kết tài khoản (thu hồi consent). Thao tác không thể hoàn tác.
     *
     * DELETE /v1/bank-accounts/{id}
     *
     * @return array<string,mixed>
     */
    public function unlink(string $accountId): array
    {
        $response = $this->http->request(
            'DELETE',
            $this->url($this->requireCfg('bankhub.base_url'), '/v1/bank-accounts/' . rawurlencode($accountId)),
            ['headers' => $this->authHeaders()]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Danh sách ngân hàng được hỗ trợ.
     *
     * GET /v1/banks
     *
     * @return array<string,mixed>
     */
    public function banks(): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('bankhub.base_url'), '/v1/banks'),
            ['headers' => $this->authHeaders()]
        );

        return is_array($response) ? $response : [];
    }
}
