<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Support;

/**
 * Tiện ích ký và xác minh chữ ký HMAC-SHA256 dùng trong HiTechCloud Pay.
 *
 * Có nhiều lược đồ chữ ký khác nhau theo từng sản phẩm (theo tài liệu):
 *
 *  - Cổng thanh toán (checkout outbound): sắp xếp key A-Z, nối `key=value`
 *    bằng `&`, ký HMAC-SHA256, xuất hex thường. Xem {@see signCheckout()}.
 *
 *  - Webhook biến động số dư (Pay API v2): chuỗi ký là `{timestamp}.{raw_body}`,
 *    header `X-HiTechCloud-Signature: sha256={hex}`, kèm chống phát lại
 *    bằng `X-HiTechCloud-Timestamp`. Xem {@see verifyWebhook()}.
 *
 *  - Webhook BankHub: HMAC-SHA256 của raw body, không tiền tố, không timestamp.
 *    Header `X-HiTechCloud-BankHub-Signature`. Xem {@see verifyRawBody()}.
 *
 *  - Webhook eInvoice: HMAC-SHA256 của raw body, header `X-HTC-Signature`.
 *    Xem {@see verifyRawBody()}.
 */
class Signature
{
    /**
     * Tính chữ ký cho form checkout cổng thanh toán.
     *
     * 1. Bỏ trường `signature` khỏi tập tham số.
     * 2. Sắp xếp key theo thứ tự alphabet (ASCII) tăng dần.
     * 3. Nối các cặp `key=value` bằng `&`.
     * 4. HMAC-SHA256 với secret_key, xuất hex thường.
     *
     * @param array<string,mixed> $params
     */
    public static function signCheckout(array $params, string $secretKey): string
    {
        unset($params['signature']);
        ksort($params, SORT_STRING);

        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . self::stringify($value);
        }

        return hash_hmac('sha256', implode('&', $parts), $secretKey);
    }

    /**
     * Xác minh chữ ký checkout (so sánh timing-safe).
     *
     * @param array<string,mixed> $params Tập tham số có kèm `signature`.
     */
    public static function verifyCheckout(array $params, string $secretKey): bool
    {
        $given = (string) ($params['signature'] ?? '');
        if ($given === '') {
            return false;
        }

        $expected = self::signCheckout($params, $secretKey);

        return hash_equals($expected, $given);
    }

    /**
     * Xác minh chữ ký webhook biến động số dư (Pay API v2).
     *
     * Chuỗi ký: `{timestamp}.{raw_body}`.
     * Header chữ ký có định dạng `sha256={hex}`.
     *
     * @param string      $payload         Raw body (nguyên văn, chưa decode).
     * @param string      $signatureHeader Giá trị header X-HiTechCloud-Signature.
     * @param string      $secret          Webhook secret.
     * @param string|int  $timestamp       Giá trị header X-HiTechCloud-Timestamp.
     * @param int         $tolerance       Dung sai timestamp (giây); 0 để bỏ qua.
     */
    public static function verifyWebhook(
        string $payload,
        string $signatureHeader,
        string $secret,
        $timestamp = '',
        int $tolerance = 300
    ): bool {
        if ($signatureHeader === '' || $secret === '') {
            return false;
        }

        $ts = (int) $timestamp;

        if ($tolerance > 0) {
            if ($ts <= 0 || abs(time() - $ts) > $tolerance) {
                return false;
            }
        }

        $signedPayload = $ts . '.' . $payload;
        $expected = 'sha256=' . hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * Xác minh chữ ký là HMAC-SHA256 thuần của raw body (BankHub, eInvoice).
     *
     * @param bool $prefixed Nếu true, chữ ký kỳ vọng có tiền tố `sha256=`.
     */
    public static function verifyRawBody(
        string $payload,
        string $signatureHeader,
        string $secret,
        bool $prefixed = false
    ): bool {
        if ($signatureHeader === '' || $secret === '') {
            return false;
        }

        $hash = hash_hmac('sha256', $payload, $secret);
        $expected = $prefixed ? 'sha256=' . $hash : $hash;

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * Tạo giá trị header chữ ký cho raw body (phục vụ test / giả lập).
     */
    public static function signRawBody(string $payload, string $secret, bool $prefixed = false): string
    {
        $hash = hash_hmac('sha256', $payload, $secret);

        return $prefixed ? 'sha256=' . $hash : $hash;
    }

    /**
     * Chuẩn hoá giá trị về chuỗi khi ghép chuỗi ký (bool -> true/false).
     *
     * @param mixed $value
     */
    private static function stringify($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
