<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * Hoá đơn điện tử (eInvoice).
 *
 * Host: einvoice-api.hitechcloud.vn.
 * Luồng: lấy token (POST /v1/token, Basic Auth, token 24h) rồi dùng Bearer.
 *
 * - POST /v1/token                                 : lấy access token.
 * - POST /v1/invoices/create                       : phát hành hoá đơn (bất đồng bộ).
 * - GET  /v1/invoices/create/check/{tracking_code} : theo dõi trạng thái phát hành.
 * - POST /v1/invoices/batch                        : phát hành hàng loạt.
 * - GET  /v1/invoices/batch/{id}                   : trạng thái lô phát hành.
 * - POST /v1/invoices/{reference_code}/cancel      : huỷ hoá đơn.
 * - POST /v1/invoices/{reference_code}/adjust      : điều chỉnh hoá đơn.
 * - POST /v1/invoices/{reference_code}/replace     : thay thế hoá đơn.
 * - GET  /v1/invoices/{reference_code}             : chi tiết hoá đơn.
 * - GET  /v1/invoices                              : danh sách hoá đơn.
 * - GET  /v1/invoices/{reference_code}/pdf | /xml  : tải file PDF / XML.
 */
class EInvoice extends TokenAwareResource
{
    protected function tokenUrl(): string
    {
        return $this->url($this->requireCfg('einvoice.base_url'), '/v1/token');
    }

    protected function tokenCredentials(): array
    {
        return [
            $this->requireCfg('einvoice.client_id'),
            $this->requireCfg('einvoice.client_secret'),
        ];
    }

    protected function parseTokenResponse(array $response): array
    {
        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;
        $token = (string) ($data['access_token'] ?? '');
        $ttl = (int) ($data['expires_in'] ?? 0);

        return [$token, $ttl];
    }

    /**
     * Lấy access token (chuỗi).
     */
    public function token(): string
    {
        return $this->accessToken(true);
    }

    /**
     * Phát hành (hoặc lưu nháp) một hoá đơn. Trả về tracking_code bất đồng bộ.
     *
     * provider_account_id sẽ tự lấy từ config nếu bạn chưa truyền.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        if (! isset($payload['provider_account_id']) && $this->cfg('einvoice.provider_account_id')) {
            $payload['provider_account_id'] = $this->cfg('einvoice.provider_account_id');
        }

        return $this->postData('/v1/invoices/create', $payload, true);
    }

    /**
     * Theo dõi trạng thái phát hành theo tracking_code.
     *
     * @return array<string,mixed>
     */
    public function checkStatus(string $trackingCode): array
    {
        return $this->getData('/v1/invoices/create/check/' . rawurlencode($trackingCode));
    }

    /**
     * Phát hành hàng loạt nhiều hoá đơn.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function createBatch(array $payload): array
    {
        return $this->postData('/v1/invoices/batch', $payload, true);
    }

    /**
     * Trạng thái một lô phát hành hàng loạt.
     *
     * @return array<string,mixed>
     */
    public function batchStatus(string $batchId): array
    {
        return $this->getData('/v1/invoices/batch/' . rawurlencode($batchId));
    }

    /**
     * Huỷ một hoá đơn theo reference_code.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function cancel(string $referenceCode, array $payload = []): array
    {
        return $this->postData('/v1/invoices/' . rawurlencode($referenceCode) . '/cancel', $payload, true);
    }

    /**
     * Điều chỉnh một hoá đơn theo reference_code.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function adjust(string $referenceCode, array $payload): array
    {
        return $this->postData('/v1/invoices/' . rawurlencode($referenceCode) . '/adjust', $payload, true);
    }

    /**
     * Thay thế một hoá đơn theo reference_code.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function replace(string $referenceCode, array $payload): array
    {
        return $this->postData('/v1/invoices/' . rawurlencode($referenceCode) . '/replace', $payload, true);
    }

    /**
     * Chi tiết một hoá đơn.
     *
     * @return array<string,mixed>
     */
    public function get(string $referenceCode): array
    {
        return $this->getData('/v1/invoices/' . rawurlencode($referenceCode));
    }

    /**
     * Danh sách hoá đơn (hỗ trợ phân trang & lọc theo tài liệu).
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed> Toàn bộ envelope (gồm `data` và `meta`).
     */
    public function list(array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('einvoice.base_url'), '/v1/invoices'),
            ['headers' => $this->authHeaders(), 'query' => $filters]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Tải nội dung PDF của hoá đơn (chuỗi nhị phân).
     */
    public function pdf(string $referenceCode): string
    {
        return (string) $this->http->request(
            'GET',
            $this->url($this->requireCfg('einvoice.base_url'), '/v1/invoices/' . rawurlencode($referenceCode) . '/pdf'),
            ['headers' => $this->authHeaders(), 'raw' => true]
        );
    }

    /**
     * Tải nội dung XML gốc (có chữ ký số) của hoá đơn.
     */
    public function xml(string $referenceCode): string
    {
        return (string) $this->http->request(
            'GET',
            $this->url($this->requireCfg('einvoice.base_url'), '/v1/invoices/' . rawurlencode($referenceCode) . '/xml'),
            ['headers' => $this->authHeaders(), 'raw' => true]
        );
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    protected function postData(string $path, array $payload, bool $idempotent = false): array
    {
        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('einvoice.base_url'), $path),
            [
                'headers' => $this->authHeaders() + ['Content-Type' => 'application/json'],
                'json' => $payload,
                'idempotent' => $idempotent,
            ]
        );

        return (array) $this->data($response);
    }

    /**
     * @return array<string,mixed>
     */
    protected function getData(string $path): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('einvoice.base_url'), $path),
            ['headers' => $this->authHeaders()]
        );

        return (array) $this->data($response);
    }
}
