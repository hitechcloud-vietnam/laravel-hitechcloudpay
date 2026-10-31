<?php

declare(strict_types=1);

namespace HiTechCloud\Waf;

use HiTechCloud\Waf\Resources\AntiTamper;
use HiTechCloud\Waf\Resources\AuthDefense;
use HiTechCloud\Waf\Resources\Certificates;
use HiTechCloud\Waf\Resources\Reports;
use HiTechCloud\Waf\Resources\Sites;
use HiTechCloud\Support\Client;

/**
 * Client VCWAF (vcwaf.hitechcloud.vn).
 *
 * - Base URL : https://vcwaf.hitechcloud.vn/api
 * - Xác thực : luồng CSRF -> login -> access token (Bearer).
 *     GET  /api/open/auth/csrf   : lấy CSRF token.
 *     POST /api/open/auth/login  : đăng nhập (username/password + csrf) -> token.
 *   Có thể cấu hình sẵn `waf.token` để bỏ qua login.
 */
class WafClient extends Client
{
    protected ?string $sessionToken = null;

    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('waf.base_url'), '/') . '/api';
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        $static = (string) ($this->cfg('waf.token') ?? '');
        if ($static !== '') {
            return $this->bearerHeader($static);
        }

        return $this->bearerHeader($this->login());
    }

    /**
     * Thực hiện luồng CSRF + login, cache token trong vòng đời instance.
     */
    public function login(bool $forceRefresh = false): string
    {
        if (! $forceRefresh && $this->sessionToken !== null && $this->sessionToken !== '') {
            return $this->sessionToken;
        }

        // 1) Lấy CSRF token.
        $csrfResponse = $this->http->request('GET', $this->url('/open/auth/csrf'));
        $csrfResponse = is_array($csrfResponse) ? $csrfResponse : [];
        $csrfData = isset($csrfResponse['data']) && is_array($csrfResponse['data'])
            ? $csrfResponse['data']
            : $csrfResponse;
        $csrf = (string) ($csrfData['csrf_token'] ?? ($csrfData['token'] ?? ''));

        // 2) Đăng nhập.
        $loginResponse = $this->http->request(
            'POST',
            $this->url('/open/auth/login'),
            [
                'headers' => array_filter([
                    'Content-Type' => 'application/json',
                    'X-CSRF-Token' => $csrf !== '' ? $csrf : null,
                ]),
                'json' => [
                    'username' => $this->requireCfg('waf.username'),
                    'password' => $this->requireCfg('waf.password'),
                    'csrf_token' => $csrf,
                ],
            ]
        );
        $loginResponse = is_array($loginResponse) ? $loginResponse : [];
        $loginData = isset($loginResponse['data']) && is_array($loginResponse['data'])
            ? $loginResponse['data']
            : $loginResponse;

        $this->sessionToken = (string) ($loginData['access_token'] ?? ($loginData['token'] ?? ''));

        return $this->sessionToken;
    }

    public function sites(): Sites
    {
        return $this->resource('sites', Sites::class);
    }

    public function certificates(): Certificates
    {
        return $this->resource('certificates', Certificates::class);
    }

    public function authDefense(): AuthDefense
    {
        return $this->resource('authDefense', AuthDefense::class);
    }

    public function reports(): Reports
    {
        return $this->resource('reports', Reports::class);
    }

    public function antiTamper(): AntiTamper
    {
        return $this->resource('antiTamper', AntiTamper::class);
    }
}
