<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

use HiTechCloud\Pay\Support\Signature;
use HiTechCloud\Pay\Webhook\WebhookPayload;

/**
 * Quản trị & xác minh Webhook.
 *
 * Đăng ký / giả lập webhook qua Admin API (my.hitechcloud.vn), xác thực bằng
 * header `Authorization: Apikey <KEY>`.
 *
 * - POST /api/v1/webhooks            : đăng ký endpoint nhận webhook.
 * - POST /api/v1/webhooks/simulate   : (sandbox) giả lập một sự kiện webhook.
 *
 * Ngoài ra cung cấp các tiện ích xác minh chữ ký và bóc tách payload cho mọi
 * loại webhook (biến động số dư, BankHub, eInvoice, cổng thanh toán).
 */
class Webhooks extends AbstractResource
{
    /**
     * Đăng ký một endpoint nhận webhook.
     *
     * Payload (theo tài liệu): url, events (mảng), authType (hmac|apikey|oauth|none),
     * secret... Tuỳ cấu hình kênh xác thực.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function register(array $payload): array
    {
        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('webhooks.admin_url'), '/api/v1/webhooks'),
            [
                'headers' => $this->adminAuth() + ['Content-Type' => 'application/json'],
                'json' => $payload,
            ]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Giả lập (simulate) một sự kiện webhook trên môi trường sandbox.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function simulate(array $payload): array
    {
        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('webhooks.admin_url'), '/api/v1/webhooks/simulate'),
            [
                'headers' => $this->adminAuth() + ['Content-Type' => 'application/json'],
                'json' => $payload,
            ]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * Xác minh chữ ký webhook biến động số dư (Pay API v2).
     *
     * @param string|int $timestamp Giá trị header X-HiTechCloud-Timestamp.
     */
    public function verify(string $rawBody, string $signatureHeader, $timestamp = '', ?string $secret = null): bool
    {
        $secret = $secret ?? (string) $this->cfg('webhook_secret');
        $tolerance = (int) $this->cfg('webhook_tolerance', 300);

        return Signature::verifyWebhook($rawBody, $signatureHeader, $secret, $timestamp, $tolerance);
    }

    /**
     * Xác minh chữ ký webhook BankHub (HMAC-SHA256 thuần của raw body).
     */
    public function verifyBankHub(string $rawBody, string $signatureHeader, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) $this->cfg('bankhub.webhook_secret');

        return Signature::verifyRawBody($rawBody, $signatureHeader, $secret);
    }

    /**
     * Xác minh chữ ký webhook eInvoice (HMAC-SHA256 thuần của raw body, header X-HTC-Signature).
     */
    public function verifyEInvoice(string $rawBody, string $signatureHeader, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) $this->cfg('einvoice.webhook_secret');

        return Signature::verifyRawBody($rawBody, $signatureHeader, $secret);
    }

    /**
     * Xác minh IPN cổng thanh toán (chữ ký HMAC-SHA256 trên raw body).
     */
    public function verifyGatewayIpn(string $rawBody, string $signatureHeader, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) $this->cfg('gateway.secret_key');

        return Signature::verifyRawBody($rawBody, $signatureHeader, $secret);
    }

    /**
     * Bóc tách raw body JSON thành đối tượng {@see WebhookPayload}.
     */
    public function parse(string $rawBody): WebhookPayload
    {
        return WebhookPayload::fromJson($rawBody);
    }

    /**
     * @return array<string,string>
     */
    protected function adminAuth(): array
    {
        return ['Authorization' => 'Apikey ' . $this->requireCfg('webhooks.admin_api_key')];
    }
}
