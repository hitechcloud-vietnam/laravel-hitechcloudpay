<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

use HiTechCloud\Pay\DataObjects\Transaction;

/**
 * Pay API v2 - Giao dịch biến động số dư (transactions).
 *
 * Host: userapi.hitechcloud.vn — xác thực Bearer access token.
 *
 * - GET /v2/transactions       : lịch sử giao dịch (lọc & phân trang).
 * - GET /v2/transactions/{id}  : chi tiết một giao dịch.
 */
class Transactions extends AbstractResource
{
    /**
     * Liệt kê giao dịch.
     *
     * Bộ lọc hỗ trợ (theo tài liệu): page, per_page, from_date, to_date,
     * account_number, code, amount_in, transfer_type (in|out).
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed> Toàn bộ envelope (gồm `data` và `meta`).
     */
    public function list(array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('core.base_url'), '/v2/transactions'),
            ['headers' => $this->coreAuth(), 'query' => $filters]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Chi tiết một giao dịch theo UUID.
     *
     * @return array<string,mixed>
     */
    public function get(string $id): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('core.base_url'), '/v2/transactions/' . rawurlencode($id)),
            ['headers' => $this->coreAuth()]
        );

        return (array) $this->data($response);
    }

    /**
     * Chi tiết một giao dịch, trả về dưới dạng DTO {@see Transaction}.
     */
    public function find(string $id): Transaction
    {
        return Transaction::fromArray($this->get($id));
    }

    /**
     * @return array<string,string>
     */
    protected function coreAuth(): array
    {
        return $this->bearerHeader($this->requireCfg('core.token'));
    }
}
