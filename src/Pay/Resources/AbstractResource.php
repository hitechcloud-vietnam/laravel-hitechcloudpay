<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

use HiTechCloud\Pay\Client\HttpClient;
use HiTechCloud\Pay\Exceptions\ConfigurationException;

/**
 * Lớp cơ sở cho mọi nhóm tài nguyên (resource) của HiTechCloud Pay.
 *
 * Giữ tham chiếu tới {@see HttpClient} và toàn bộ cấu hình, đồng thời cung cấp
 * các tiện ích dựng header xác thực và truy cập cấu hình an toàn.
 */
abstract class AbstractResource
{
    protected HttpClient $http;

    /** @var array<string,mixed> */
    protected array $config;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(HttpClient $http, array $config)
    {
        $this->http = $http;
        $this->config = $config;
    }

    /**
     * Lấy giá trị cấu hình theo đường dẫn dạng "dot" (ví dụ "gateway.secret_key").
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

    /**
     * Lấy giá trị cấu hình bắt buộc; ném lỗi nếu rỗng.
     */
    protected function requireCfg(string $key): string
    {
        $value = $this->cfg($key);

        if ($value === null || $value === '') {
            throw new ConfigurationException(
                "Thiếu cấu hình bắt buộc [{$key}] cho HiTechCloud Pay. " .
                'Vui lòng khai báo trong file config/hitechcloudpay.php hoặc biến môi trường tương ứng.'
            );
        }

        return (string) $value;
    }

    /**
     * Dựng header Authorization dạng Basic.
     *
     * @return array<string,string>
     */
    protected function basicHeader(string $username, string $password): array
    {
        return ['Authorization' => 'Basic ' . base64_encode($username . ':' . $password)];
    }

    /**
     * Dựng header Authorization dạng Bearer.
     *
     * @return array<string,string>
     */
    protected function bearerHeader(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Rút gọn: lấy trường `data` trong envelope nếu có, nếu không trả nguyên body.
     *
     * @param array<string,mixed>|string $response
     * @return mixed
     */
    protected function data($response)
    {
        if (is_array($response) && array_key_exists('data', $response)) {
            return $response['data'];
        }

        return $response;
    }

    /**
     * Ghép base URL với path, xử lý dấu gạch chéo thừa.
     */
    protected function url(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}
