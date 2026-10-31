<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * Pay API v2 - Tài khoản ảo / Virtual Account (sub-accounts).
 *
 * Host: userapi.hitechcloud.vn — xác thực Bearer access token.
 *
 * - GET    /v2/sub-accounts        : danh sách VA.
 * - POST   /v2/sub-accounts        : tạo VA cho đơn hàng / khách hàng.
 * - GET    /v2/sub-accounts/{id}   : chi tiết VA và lịch sử thu tiền.
 * - DELETE /v2/sub-accounts/{id}   : huỷ / đóng VA.
 */
class VirtualAccounts extends AbstractResource
{
    /**
     * Danh sách tài khoản ảo (hỗ trợ page, per_page, status).
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed> Toàn bộ envelope (gồm `data` và `meta`).
     */
    public function list(array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('core.base_url'), '/v2/sub-accounts'),
            ['headers' => $this->coreAuth(), 'query' => $filters]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Tạo mới một tài khoản ảo.
     *
     * Payload (theo tài liệu): bank_code, account_number_primary,
     * sub_account_name, sub_account_code, max_amount, expired_at.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('core.base_url'), '/v2/sub-accounts'),
            [
                'headers' => $this->coreAuth() + ['Content-Type' => 'application/json'],
                'json' => $payload,
                'idempotent' => true,
            ]
        );

        return (array) $this->data($response);
    }

    /**
     * Chi tiết một tài khoản ảo theo UUID.
     *
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('core.base_url'), '/v2/sub-accounts/' . rawurlencode($id)),
            ['headers' => $this->coreAuth()]
        );

        return (array) $this->data($response);
    }

    /**
     * Huỷ / đóng một tài khoản ảo.
     *
     * @return array<string,mixed>
     */
    public function delete(string $id): array
    {
        $response = $this->http->request(
            'DELETE',
            $this->url($this->requireCfg('core.base_url'), '/v2/sub-accounts/' . rawurlencode($id)),
            ['headers' => $this->coreAuth()]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * @return array<string,string>
     */
    protected function coreAuth(): array
    {
        return $this->bearerHeader($this->requireCfg('core.token'));
    }
}
