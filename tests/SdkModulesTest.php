<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use HiTechCloud\Crm\CrmClient;
use HiTechCloud\HiTechCloud;
use HiTechCloud\Http\HttpClient;
use HiTechCloud\Omni\OmniClient;
use HiTechCloud\Pos\PosClient;
use HiTechCloud\Tools\ToolsClient;
use PHPUnit\Framework\TestCase;

/**
 * Kiểm thử các module bổ sung (POS, Omni, CRM, Tools) KHÔNG gọi mạng thật.
 */
class SdkModulesTest extends TestCase
{
    /** @var array<int,array<string,mixed>> */
    protected array $history = [];

    /**
     * @param array<int,Response> $responses
     */
    protected function mockHttp(array $responses): HttpClient
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));

        return new HttpClient(new GuzzleClient(['handler' => $stack]), 5, 0);
    }

    protected function config(): array
    {
        return [
            'pos' => ['base_url' => 'https://pos.hitechcloud.vn', 'token' => 'POS_TOKEN'],
            'omni' => ['base_url' => 'https://omni.hitechcloud.vn', 'token' => 'OMNI_TOKEN'],
            'crm' => ['base_url' => 'https://crm-gateways.hitechcloud.vn', 'token' => 'CRM_TOKEN'],
            'tools' => ['base_url' => 'https://api-tools.hitechcloud.vn', 'api_key' => 'TOOLS_KEY'],
        ];
    }

    public function test_pos_orders_list_uses_bearer_and_shop_scoped_path(): void
    {
        $body = json_encode(['success' => true, 'data' => [['order_id' => 'order_1']]]);
        $http = $this->mockHttp([new Response(200, [], $body)]);

        $pos = new PosClient($this->config(), $http);
        $result = $pos->orders()->list('shop_0a1b2c3d', ['page' => 1]);

        $this->assertSame('order_1', $result['data'][0]['order_id']);

        $request = $this->history[0]['request'];
        $this->assertSame('Bearer POS_TOKEN', $request->getHeaderLine('Authorization'));
        $this->assertStringContainsString('/api/v1/shops/shop_0a1b2c3d/orders', (string) $request->getUri());
        $this->assertStringContainsString('page=1', $request->getUri()->getQuery());
    }

    public function test_omni_send_message_posts_json(): void
    {
        $body = json_encode(['data' => ['id' => 'msg_1']]);
        $http = $this->mockHttp([new Response(200, [], $body)]);

        $omni = new OmniClient($this->config(), $http);
        $result = $omni->conversations()->sendMessage('cnv_1', ['text' => 'Xin chào']);

        $this->assertSame('msg_1', $result['data']['id']);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('Bearer OMNI_TOKEN', $request->getHeaderLine('Authorization'));
        $this->assertStringContainsString('/v1/conversations/cnv_1/messages', (string) $request->getUri());
    }

    public function test_tools_uses_api_key_header(): void
    {
        $body = json_encode(['success' => true, 'data' => ['country' => 'US']]);
        $http = $this->mockHttp([new Response(200, [], $body)]);

        $tools = new ToolsClient($this->config(), $http);
        $result = $tools->geoIp()->lookup('8.8.8.8');

        $this->assertSame('US', $result['data']['country']);

        $request = $this->history[0]['request'];
        $this->assertSame('TOOLS_KEY', $request->getHeaderLine('X-API-Key'));
        $this->assertStringContainsString('/api/geoip/lookup', (string) $request->getUri());
        $this->assertStringContainsString('ip=8.8.8.8', $request->getUri()->getQuery());
    }

    public function test_crm_oauth_token_is_fetched_then_used(): void
    {
        $token = json_encode(['data' => ['access_token' => 'OAUTH_XYZ', 'expires_in' => 3600]]);
        $list = json_encode(['data' => [['id' => 'con_1']]]);
        $http = $this->mockHttp([
            new Response(200, [], $token),
            new Response(200, [], $list),
        ]);

        // Không có token tĩnh -> buộc đi qua luồng OAuth2.
        $config = $this->config();
        unset($config['crm']['token']);
        $config['crm']['client_id'] = 'cid';
        $config['crm']['client_secret'] = 'csecret';

        $crm = new CrmClient($config, $http);
        $result = $crm->contacts()->list();

        $this->assertSame('con_1', $result['data'][0]['id']);

        // Request đầu: lấy token. Request sau: dùng Bearer token vừa lấy.
        $this->assertStringContainsString('/v1/oauth/token', (string) $this->history[0]['request']->getUri());
        $this->assertSame('Bearer OAUTH_XYZ', $this->history[1]['request']->getHeaderLine('Authorization'));
    }

    public function test_manager_resolves_all_modules(): void
    {
        $manager = new HiTechCloud($this->config());

        $this->assertInstanceOf(PosClient::class, $manager->pos());
        $this->assertInstanceOf(OmniClient::class, $manager->omni());
        $this->assertInstanceOf(CrmClient::class, $manager->crm());
        $this->assertInstanceOf(ToolsClient::class, $manager->tools());
        $this->assertInstanceOf(\HiTechCloud\Pay\HiTechCloudPay::class, $manager->pay());

        // Memoized.
        $this->assertSame($manager->pos(), $manager->pos());
    }
}
