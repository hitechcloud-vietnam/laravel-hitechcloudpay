<?php

declare(strict_types=1);

namespace HiTechCloud\Support;

use HiTechCloud\Exceptions\ConfigurationException;
use HiTechCloud\Http\HttpClient;

/**
 * Lớp Client cơ sở dùng chung cho các module (POS, Omni, CRM, WAF, Sale AI,
 * Tools, UserAPI, eKYC).
 *
 * Mỗi module kế thừa và cấu hình:
 *  - {@see baseUrl()}      : host + tiền tố đường dẫn (ví dụ /api/v1).
 *  - {@see authHeaders()}  : header xác thực (Bearer / X-API-Key / ...).
 *
 * Các Resource gọi {@see get()} / {@see post()} ... thông qua Client này; Client
 * tự ghép base URL, gắn header auth và uỷ quyền cho {@see HttpClient}.
 */
abstract class Client
{
    /** @var array<string,mixed> */
    protected array $config;

    protected HttpClient $http;

    /** @var array<string,object> */
    protected array $resources = [];

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config, ?HttpClient $http = null)
    {
        $this->config = $config;
        $this->http = $http ?: new HttpClient(
            null,
            (int) ($config['timeout'] ?? 15),
            (int) ($config['retries'] ?? 2)
        );
    }

    /**
     * Host + tiền tố đường dẫn cho module (không có dấu `/` ở cuối).
     */
    abstract protected function baseUrl(): string;

    /**
     * Header xác thực mặc định. Module override khi cần luồng riêng (token form,
     * OAuth2, CSRF + login...).
     *
     * @return array<string,string>
     */
    abstract protected function authHeaders(): array;

    /**
     * @param array<string,mixed> $options
     * @return array<string,mixed>|string
     */
    public function request(string $method, string $path, array $options = [])
    {
        $headers = $this->authHeaders() + (array) ($options['headers'] ?? []);
        $options['headers'] = $headers;

        return $this->http->request($method, $this->url($path), $options);
    }

    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>|string
     */
    public function get(string $path, array $query = [])
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    /**
     * @param array<string,mixed> $json
     * @return array<string,mixed>|string
     */
    public function post(string $path, array $json = [], bool $idempotent = false)
    {
        return $this->request('POST', $path, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $json,
            'idempotent' => $idempotent,
        ]);
    }

    /**
     * @param array<string,mixed> $json
     * @return array<string,mixed>|string
     */
    public function put(string $path, array $json = [])
    {
        return $this->request('PUT', $path, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $json,
        ]);
    }

    /**
     * @param array<string,mixed> $json
     * @return array<string,mixed>|string
     */
    public function patch(string $path, array $json = [])
    {
        return $this->request('PATCH', $path, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $json,
        ]);
    }

    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>|string
     */
    public function delete(string $path, array $query = [])
    {
        return $this->request('DELETE', $path, ['query' => $query]);
    }

    /**
     * Lớp HttpClient bên dưới (hữu ích khi test).
     */
    public function http(): HttpClient
    {
        return $this->http;
    }

    /**
     * @return array<string,mixed>
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Lấy giá trị cấu hình theo đường dẫn "dot".
     *
     * @param mixed $default
     * @return mixed
     */
    protected function cfg(string $key, $default = null)
    {
        $value = $this->config;

        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    protected function requireCfg(string $key): string
    {
        $value = $this->cfg($key);

        if ($value === null || $value === '') {
            throw new ConfigurationException(
                "Thiếu cấu hình bắt buộc [{$key}] cho HiTechCloud SDK. " .
                'Vui lòng khai báo trong config/hitechcloudpay.php hoặc biến môi trường tương ứng.'
            );
        }

        return (string) $value;
    }

    protected function url(string $path): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * @return array<string,string>
     */
    protected function bearerHeader(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * @return array<string,string>
     */
    protected function basicHeader(string $username, string $password): array
    {
        return ['Authorization' => 'Basic ' . base64_encode($username . ':' . $password)];
    }

    /**
     * Khởi tạo lười và cache một resource (truyền chính Client vào constructor).
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    protected function resource(string $key, string $class)
    {
        if (! isset($this->resources[$key])) {
            $this->resources[$key] = new $class($this);
        }

        return $this->resources[$key];
    }
}
