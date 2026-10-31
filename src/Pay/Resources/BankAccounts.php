<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * Pay API v2 - Tài khoản ngân hàng (bank-accounts).
 *
 * Host: userapi.hitechcloud.vn — xác thực Bearer access token.
 *
 * - GET /v2/bank-accounts        : danh sách tài khoản đã liên kết.
 * - GET /v2/bank-accounts/{id}   : chi tiết một tài khoản (gồm số dư luỹ kế).
 */
class BankAccounts extends AbstractResource
{
    /**
     * Danh sách tài khoản ngân hàng (hỗ trợ page, per_page, active).
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed> Toàn bộ envelope (gồm `data` và `meta`).
     */
    public function list(array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('core.base_url'), '/v2/bank-accounts'),
            ['headers' => $this->coreAuth(), 'query' => $filters]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Chi tiết một tài khoản ngân hàng theo UUID.
     *
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('core.base_url'), '/v2/bank-accounts/' . rawurlencode($id)),
            ['headers' => $this->coreAuth()]
        );

        return (array) $this->data($response);
    }

    /**
     * @return array<string,string>
     */
    protected function coreAuth(): array
    {
        return $this->bearerHeader($this->requireCfg('core.token'));
    }
}
