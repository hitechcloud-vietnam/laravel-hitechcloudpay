<?php

declare(strict_types=1);

namespace HiTechCloud\Support;

/**
 * Tiện ích ký / xác minh chữ ký HMAC-SHA256 dùng chung cho các module emit
 * webhook (Omni X-HTC-Signature, CRM X-CRM-Signature, Sale AI X-SaleAI-Signature,
 * POS X-HitechPos-Signature).
 *
 * Các webhook này ký HMAC-SHA256 trên raw body; một số có tiền tố `sha256=`.
 */
class Signature
{
    /**
     * Xác minh chữ ký HMAC-SHA256 của raw body (timing-safe).
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
}
