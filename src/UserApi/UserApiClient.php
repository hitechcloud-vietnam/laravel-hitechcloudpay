<?php

declare(strict_types=1);

namespace HiTechCloud\UserApi;

use HiTechCloud\UserApi\Resources\Account;
use HiTechCloud\UserApi\Resources\Domains;
use HiTechCloud\UserApi\Resources\Services;
use HiTechCloud\Support\Client;

/**
 * Client User API (api.hitechcloud.vn / ote.hitechcloud.vn khi sandbox).
 *
 * - Base URL : https://api.hitechcloud.vn (sandbox: https://ote.hitechcloud.vn)
 * - Xác thực : POST /api/login (username/password) -> Bearer token; có thể cấu
 *   hình sẵn `userapi.token` để bỏ qua login.
 */
class UserApiClient extends Client
{
    protected ?string $sessionToken = null;

    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('userapi.base_url'), '/');
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        $static = (string) ($this->cfg('userapi.token') ?? '');
        if ($static !== '') {
            return $this->bearerHeader($static);
        }

        return $this->bearerHeader($this->login());
    }

    /**
     * Đăng nhập lấy Bearer token (cache trong vòng đời instance).
     */
    public function login(bool $forceRefresh = false): string
    {
        if (! $forceRefresh && $this->sessionToken !== null && $this->sessionToken !== '') {
            return $this->sessionToken;
        }

        $response = $this->http->request(
            'POST',
            $this->url('/api/login'),
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'username' => $this->requireCfg('userapi.username'),
                    'password' => $this->requireCfg('userapi.password'),
                ],
            ]
        );
        $response = is_array($response) ? $response : [];
        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;

        $this->sessionToken = (string) ($data['token'] ?? ($data['access_token'] ?? ''));

        return $this->sessionToken;
    }

    public function account(): Account
    {
        return $this->resource('account', Account::class);
    }

    public function services(): Services
    {
        return $this->resource('services', Services::class);
    }

    public function domains(): Domains
    {
        return $this->resource('domains', Domains::class);
    }
}
