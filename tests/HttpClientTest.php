<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use HiTechCloud\Pay\Client\HttpClient;
use HiTechCloud\Pay\Exceptions\ApiException;
use HiTechCloud\Pay\HiTechCloudPay;
use PHPUnit\Framework\TestCase;

/**
 * Kiểm thử tầng HTTP mà KHÔNG gọi mạng thật (dùng Guzzle MockHandler).
 */
class HttpClientTest extends TestCase
{
    /** @var array<int,array<string,mixed>> */
    protected array $history = [];

    /**
     * Dựng một client HiTechCloudPay với hàng đợi phản hồi giả.
     *
     * @param array<int,Response> $responses
     * @param array<string,mixed> $configOverrides
     */
    protected function makeClient(array $responses, array $configOverrides = []): HiTechCloudPay
    {
        $this->history = [];

        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));

        $guzzle = new GuzzleClient(['handler' => $stack]);
        $http = new HttpClient($guzzle, 5, 2);

        $config = array_replace_recursive([
            'timeout' => 5,
            'retries' => 2,
            'core' => [
                'token' => 'TEST_TOKEN',
                'base_url' => 'https://userapi.hitechcloud.vn',
            ],
            'gateway' => [
                'merchant_id' => 'HTC_MCH_123456',
                'secret_key' => 'secret',
                'api_url' => 'https://pgapi.hitechcloud.vn',
                'checkout_url' => 'https://pay-gateways.hitechcloud.vn',
            ],
        ], $configOverrides);

        return new HiTechCloudPay($config, $http);
    }

    public function test_bank_accounts_list_parses_envelope_and_sends_bearer(): void
    {
        $body = json_encode([
            'status' => 'success',
            'data' => [['id' => 'abc', 'account_number' => '9988776655']],
            'meta' => ['pagination' => ['total' => 1, 'has_more' => false]],
        ]);

        $client = $this->makeClient([new Response(200, [], $body)]);

        $result = $client->bankAccounts()->list(['page' => 1]);

        $this->assertSame('abc', $result['data'][0]['id']);
        $this->assertFalse($result['meta']['pagination']['has_more']);

        // Kiểm tra header Authorization Bearer và query được gửi đúng.
        $request = $this->history[0]['request'];
        $this->assertSame('Bearer TEST_TOKEN', $request->getHeaderLine('Authorization'));
        $this->assertStringContainsString('page=1', $request->getUri()->getQuery());
        $this->assertStringContainsString('/v2/bank-accounts', (string) $request->getUri());
    }

    public function test_error_envelope_throws_api_exception(): void
    {
        $body = json_encode([
            'status' => 'error',
            'code' => 'UNAUTHORIZED',
            'message' => 'Bearer Token không hợp lệ hoặc đã bị thu hồi.',
        ]);

        $client = $this->makeClient([new Response(401, [], $body)]);

        try {
            $client->bankAccounts()->get('xxx');
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $e) {
            $this->assertSame(401, $e->getStatusCode());
            $this->assertSame('UNAUTHORIZED', $e->getErrorCode());
            $this->assertTrue($e->isUnauthorized());
        }
    }

    public function test_retries_on_429_then_succeeds(): void
    {
        $ok = json_encode(['status' => 'success', 'data' => ['id' => 't1']]);

        $client = $this->makeClient([
            new Response(429, ['Retry-After' => '0'], '{"status":"error","code":"TOO_MANY_REQUESTS"}'),
            new Response(200, [], $ok),
        ]);

        $tx = $client->transactions()->get('t1');

        $this->assertSame('t1', $tx['id']);
        $this->assertCount(2, $this->history); // đã thử lại 1 lần
    }

    public function test_gateway_refund_sends_idempotency_key_and_basic_auth(): void
    {
        $body = json_encode(['status' => 'success', 'data' => ['order_status' => 'PARTIALLY_REFUNDED']]);
        $client = $this->makeClient([new Response(200, [], $body)]);

        $result = $client->gateway()->refund('INV-1', 50000, 'Thieu hang', 'RF-1');

        $this->assertSame('PARTIALLY_REFUNDED', $result['order_status']);

        $request = $this->history[0]['request'];
        $this->assertStringStartsWith('Basic ', $request->getHeaderLine('Authorization'));
        $this->assertNotEmpty($request->getHeaderLine('Idempotency-Key'));
        $this->assertStringContainsString('/v1/orders/INV-1/refund', (string) $request->getUri());
    }

    public function test_create_session_builds_signed_payload(): void
    {
        $client = $this->makeClient([]); // không gọi mạng

        $session = $client->gateway()->createSession([
            'order_invoice_number' => 'INV-1',
            'order_amount' => 100000,
            'order_description' => 'Test',
            'success_url' => 'https://e.com/s',
            'error_url' => 'https://e.com/e',
            'cancel_url' => 'https://e.com/c',
        ]);

        $this->assertSame('https://pay-gateways.hitechcloud.vn/v1/checkout/init', $session->checkoutUrl);
        $this->assertSame('PURCHASE', $session->params['operation']);
        $this->assertSame('VND', $session->params['order_currency']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $session->signature());
    }
}
