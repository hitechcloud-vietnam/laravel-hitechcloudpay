<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Tests;

use HiTechCloud\Pay\Support\Signature;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    public function test_sign_checkout_is_deterministic_and_order_independent(): void
    {
        $secret = 'super_secret_key';

        $a = Signature::signCheckout([
            'merchant_id' => 'HTC_MCH_123456',
            'operation' => 'PURCHASE',
            'order_amount' => 250000,
            'order_currency' => 'VND',
        ], $secret);

        // Cùng tham số nhưng khác thứ tự -> cùng chữ ký.
        $b = Signature::signCheckout([
            'order_currency' => 'VND',
            'order_amount' => 250000,
            'operation' => 'PURCHASE',
            'merchant_id' => 'HTC_MCH_123456',
        ], $secret);

        $this->assertSame($a, $b);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $a);
    }

    public function test_sign_checkout_matches_documented_algorithm(): void
    {
        $secret = 'secret';
        $params = ['b' => '2', 'a' => '1', 'signature' => 'ignored'];

        // Chuỗi ký kỳ vọng: "a=1&b=2" (đã bỏ signature, sắp xếp A-Z).
        $expected = hash_hmac('sha256', 'a=1&b=2', $secret);

        $this->assertSame($expected, Signature::signCheckout($params, $secret));
    }

    public function test_verify_checkout(): void
    {
        $secret = 'secret';
        $params = ['merchant_id' => 'HTC', 'order_amount' => 1000];
        $params['signature'] = Signature::signCheckout($params, $secret);

        $this->assertTrue(Signature::verifyCheckout($params, $secret));

        $params['order_amount'] = 9999; // bị sửa
        $this->assertFalse(Signature::verifyCheckout($params, $secret));
    }

    public function test_verify_webhook_valid_and_invalid(): void
    {
        $secret = 'whsec';
        $body = '{"event":"transaction.in","transferAmount":500000}';
        $ts = time();

        $sig = 'sha256=' . hash_hmac('sha256', $ts . '.' . $body, $secret);

        $this->assertTrue(Signature::verifyWebhook($body, $sig, $secret, $ts, 300));

        // Sai secret
        $this->assertFalse(Signature::verifyWebhook($body, $sig, 'wrong', $ts, 300));

        // Body bị sửa
        $this->assertFalse(Signature::verifyWebhook($body . 'x', $sig, $secret, $ts, 300));
    }

    public function test_verify_webhook_rejects_replay(): void
    {
        $secret = 'whsec';
        $body = '{"event":"transaction.in"}';
        $ts = time() - 1000; // quá hạn so với tolerance 300

        $sig = 'sha256=' . hash_hmac('sha256', $ts . '.' . $body, $secret);

        $this->assertFalse(Signature::verifyWebhook($body, $sig, $secret, $ts, 300));
        // Nhưng nếu tắt kiểm tra timestamp (tolerance=0) thì hợp lệ.
        $this->assertTrue(Signature::verifyWebhook($body, $sig, $secret, $ts, 0));
    }

    public function test_verify_raw_body_bankhub_einvoice_style(): void
    {
        $secret = 'raw_secret';
        $body = '{"event":"TRANSACTION_CREATED"}';
        $sig = hash_hmac('sha256', $body, $secret);

        $this->assertTrue(Signature::verifyRawBody($body, $sig, $secret));
        $this->assertFalse(Signature::verifyRawBody($body, 'deadbeef', $secret));
    }
}
