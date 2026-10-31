<?php

declare(strict_types=1);

namespace HiTechCloud\Ekyc;

use HiTechCloud\Exceptions\ConfigurationException;

/**
 * Tiện ích ký số & mã hoá cho cổng eKYC/VNeID (ekyc-c06.hitechcloud.vn).
 *
 * Cổng C06 yêu cầu agent ký request bằng RSA (SHA256withRSA) và có thể mã hoá
 * payload bằng AES. Lớp này cung cấp:
 *  - {@see signRsa()}     : ký SHA256withRSA, trả chữ ký base64.
 *  - {@see verifyRsa()}   : xác minh chữ ký RSA của phản hồi/callback.
 *  - {@see encryptAes()} / {@see decryptAes()} : AES-256-CBC tiện dụng.
 *
 * LƯU Ý: lược đồ chính xác (thuật toán, padding, thứ tự field) phụ thuộc hợp
 * đồng tích hợp với C06; các tham số dưới đây là điểm khởi đầu hợp lý và cần
 * đối chiếu lại khi có credentials thật. Module eKYC vẫn tắt tới khi cấu hình
 * `ekyc.*` đầy đủ.
 */
class Crypto
{
    /**
     * Ký SHA256withRSA, trả chữ ký base64.
     *
     * @param string $privateKeyPem Private key dạng PEM (PKCS#1 hoặc PKCS#8).
     */
    public static function signRsa(string $data, string $privateKeyPem): string
    {
        $key = openssl_pkey_get_private($privateKeyPem);
        if ($key === false) {
            throw new ConfigurationException('eKYC: private key RSA không hợp lệ.');
        }

        $signature = '';
        $ok = openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);

        if ($ok === false) {
            throw new ConfigurationException('eKYC: không thể ký dữ liệu bằng RSA.');
        }

        return base64_encode($signature);
    }

    /**
     * Xác minh chữ ký SHA256withRSA (chữ ký truyền vào dạng base64).
     *
     * @param string $publicKeyPem Public key C06 dạng PEM.
     */
    public static function verifyRsa(string $data, string $base64Signature, string $publicKeyPem): bool
    {
        $key = openssl_pkey_get_public($publicKeyPem);
        if ($key === false) {
            return false;
        }

        $signature = base64_decode($base64Signature, true);
        if ($signature === false) {
            return false;
        }

        return openssl_verify($data, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Mã hoá AES-256-CBC, trả base64(iv + ciphertext).
     *
     * @param string $key Khoá 32 byte (hoặc sẽ được hash SHA-256 để đủ độ dài).
     */
    public static function encryptAes(string $plaintext, string $key): string
    {
        $key = self::normalizeKey($key);
        $iv = random_bytes(16);

        $cipher = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            throw new ConfigurationException('eKYC: không thể mã hoá AES.');
        }

        return base64_encode($iv . $cipher);
    }

    /**
     * Giải mã chuỗi base64(iv + ciphertext) bằng AES-256-CBC.
     */
    public static function decryptAes(string $base64, string $key): string
    {
        $key = self::normalizeKey($key);
        $raw = base64_decode($base64, true);
        if ($raw === false || strlen($raw) <= 16) {
            throw new ConfigurationException('eKYC: dữ liệu AES không hợp lệ.');
        }

        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);

        $plain = openssl_decrypt($cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            throw new ConfigurationException('eKYC: không thể giải mã AES.');
        }

        return $plain;
    }

    private static function normalizeKey(string $key): string
    {
        return strlen($key) === 32 ? $key : hash('sha256', $key, true);
    }
}
