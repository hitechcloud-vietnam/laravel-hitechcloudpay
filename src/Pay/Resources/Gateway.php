<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

use HiTechCloud\Pay\DataObjects\PaymentSession;
use HiTechCloud\Pay\Support\Signature;

/**
 * Cổng thanh toán (Payment Gateway).
 *
 * - Tạo phiên thanh toán / checkout (QR VietQR-NAPAS, thẻ quốc tế, ví...):
 *   POST {checkout_url}/v1/checkout/init (form submit từ trình duyệt, có ký HMAC).
 * - Tra cứu đơn:   GET  {api_url}/v1/orders/{order_invoice_number}
 * - Hoàn tiền:     POST {api_url}/v1/orders/{order_invoice_number}/refund
 * - Liệt kê/đối soát: GET {api_url}/v1/orders
 *
 * Xác thực REST API: Basic base64(merchant_id:secret_key).
 *
 * Ghi chú thiết kế: tài liệu không cung cấp endpoint "huỷ đơn" chủ động qua API
 * (trạng thái CANCELLED chỉ phát sinh khi khách tự huỷ và được chuyển về
 * `cancel_url`), nên SDK không expose phương thức cancel() để tránh bịa endpoint.
 */
class Gateway extends AbstractResource
{
    /**
     * Tạo phiên thanh toán và trả về {@see PaymentSession} đã ký.
     *
     * Các tham số bắt buộc (theo tài liệu): order_invoice_number, order_amount,
     * order_description, success_url, error_url, cancel_url. Các trường
     * merchant_id, operation=PURCHASE, order_currency=VND sẽ được tự điền nếu
     * bạn chưa truyền.
     *
     * @param array<string,mixed> $params
     */
    public function createSession(array $params): PaymentSession
    {
        $merchantId = $this->requireCfg('gateway.merchant_id');
        $secretKey = $this->requireCfg('gateway.secret_key');

        $params = array_merge([
            'merchant_id' => $merchantId,
            'operation' => 'PURCHASE',
            'order_currency' => 'VND',
        ], $params);

        $params['signature'] = Signature::signCheckout($params, $secretKey);

        $checkoutUrl = $this->url($this->requireCfg('gateway.checkout_url'), '/v1/checkout/init');

        return new PaymentSession($checkoutUrl, $params);
    }

    /**
     * Tra cứu chi tiết đơn hàng theo mã hoá đơn.
     *
     * GET /v1/orders/{order_invoice_number}
     *
     * @return array<string,mixed>
     */
    public function getOrder(string $orderInvoiceNumber): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('gateway.api_url'), '/v1/orders/' . rawurlencode($orderInvoiceNumber)),
            ['headers' => $this->gatewayAuth()]
        );

        return (array) $this->data($response);
    }

    /**
     * Lấy nhanh trạng thái đơn (order_status), ví dụ CAPTURED / PENDING...
     */
    public function status(string $orderInvoiceNumber): ?string
    {
        $order = $this->getOrder($orderInvoiceNumber);

        return isset($order['order_status']) ? (string) $order['order_status'] : null;
    }

    /**
     * Hoàn tiền một phần hoặc toàn phần cho đơn đã CAPTURED.
     *
     * POST /v1/orders/{order_invoice_number}/refund
     *
     * @param int|null    $amount          Số tiền hoàn (VNĐ); null = hoàn toàn bộ.
     * @param string|null $reason          Lý do hoàn tiền (đối soát).
     * @param string|null $refundReference Mã tham chiếu phía bạn (chống trùng).
     * @return array<string,mixed>
     */
    public function refund(
        string $orderInvoiceNumber,
        ?int $amount = null,
        ?string $reason = null,
        ?string $refundReference = null
    ): array {
        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = $amount;
        }
        if ($reason !== null) {
            $payload['reason'] = $reason;
        }
        if ($refundReference !== null) {
            $payload['refund_reference'] = $refundReference;
        }

        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('gateway.api_url'), '/v1/orders/' . rawurlencode($orderInvoiceNumber) . '/refund'),
            [
                'headers' => $this->gatewayAuth() + ['Content-Type' => 'application/json'],
                'json' => $payload,
                'idempotent' => true,
            ]
        );

        return (array) $this->data($response);
    }

    /**
     * Liệt kê đơn hàng phục vụ đối soát (hỗ trợ page, per_page, from_date, to_date).
     *
     * GET /v1/orders
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed> Toàn bộ envelope (gồm `data` và `meta`).
     */
    public function listOrders(array $filters = []): array
    {
        $response = $this->http->request(
            'GET',
            $this->url($this->requireCfg('gateway.api_url'), '/v1/orders'),
            [
                'headers' => $this->gatewayAuth(),
                'query' => $filters,
            ]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Header Basic cho REST API cổng thanh toán.
     *
     * @return array<string,string>
     */
    protected function gatewayAuth(): array
    {
        return $this->basicHeader(
            $this->requireCfg('gateway.merchant_id'),
            $this->requireCfg('gateway.secret_key')
        );
    }
}
