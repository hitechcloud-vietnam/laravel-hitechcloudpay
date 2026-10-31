<?php

declare(strict_types=1);

namespace HiTechCloud\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use HiTechCloud\Exceptions\ApiException;
use Psr\Http\Message\ResponseInterface;

/**
 * Lớp vận chuyển HTTP dùng chung cho mọi module của HiTechCloud SDK (trừ Pay –
 * module này giữ {@see \HiTechCloud\Pay\Client\HttpClient} riêng để tương thích
 * ngược). Hành vi đồng nhất với lớp của Pay:
 *
 *  - Gắn header JSON và Idempotency-Key cho thao tác ghi.
 *  - Thử lại tự động khi gặp 429 / 5xx hoặc lỗi kết nối (exponential backoff).
 *  - Giải mã envelope JSON và ném {@see ApiException} khi có lỗi.
 *
 * Phần xác thực (Authorization) do từng Client/Resource tự dựng và truyền vào
 * qua khoá `headers`, nên lớp này không cần biết lược đồ auth cụ thể.
 */
class HttpClient
{
    protected GuzzleClient $guzzle;

    protected int $timeout;

    protected int $retries;

    public function __construct(?GuzzleClient $guzzle = null, int $timeout = 15, int $retries = 2)
    {
        $this->guzzle = $guzzle ?: new GuzzleClient();
        $this->timeout = $timeout;
        $this->retries = max(0, $retries);
    }

    /**
     * Thực hiện một request và trả về body đã giải mã (mảng) hoặc chuỗi thô.
     *
     * Khoá hỗ trợ trong $options: query, json, form, body, headers, idempotent, raw.
     *
     * @param array<string,mixed> $options
     * @return array<string,mixed>|string
     */
    public function request(string $method, string $url, array $options = [])
    {
        $method = strtoupper($method);
        $raw = (bool) ($options['raw'] ?? false);

        $headers = (array) ($options['headers'] ?? []);
        $headers += [
            'Accept' => 'application/json',
            'User-Agent' => 'laravel-hitechcloud-sdk/1.0',
        ];

        if (($options['idempotent'] ?? false) && ! isset($headers['Idempotency-Key'])) {
            $headers['Idempotency-Key'] = $this->generateIdempotencyKey();
        }

        $guzzleOptions = [
            RequestOptions::TIMEOUT => $this->timeout,
            RequestOptions::HEADERS => $headers,
            RequestOptions::HTTP_ERRORS => false,
        ];

        if (! empty($options['query'])) {
            $guzzleOptions[RequestOptions::QUERY] = $options['query'];
        }

        if (array_key_exists('json', $options)) {
            $guzzleOptions[RequestOptions::JSON] = $options['json'];
        } elseif (array_key_exists('form', $options)) {
            $guzzleOptions[RequestOptions::FORM_PARAMS] = $options['form'];
        } elseif (array_key_exists('body', $options)) {
            $guzzleOptions[RequestOptions::BODY] = $options['body'];
        }

        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $response = $this->guzzle->request($method, $url, $guzzleOptions);
            } catch (ConnectException $e) {
                if ($attempt <= $this->retries) {
                    $this->backoff($attempt);
                    continue;
                }
                throw new ApiException(
                    'Không thể kết nối tới HiTechCloud: ' . $e->getMessage(),
                    0,
                    null,
                    [],
                    $e
                );
            } catch (RequestException $e) {
                $response = $e->getResponse();
                if ($response === null) {
                    if ($attempt <= $this->retries) {
                        $this->backoff($attempt);
                        continue;
                    }
                    throw new ApiException('Lỗi gọi HiTechCloud: ' . $e->getMessage(), 0, null, [], $e);
                }
            } catch (GuzzleException $e) {
                throw new ApiException('Lỗi gọi HiTechCloud: ' . $e->getMessage(), 0, null, [], $e);
            }

            $status = $response->getStatusCode();

            if (($status === 429 || $status >= 500) && $attempt <= $this->retries) {
                $this->backoff($attempt, $response);
                continue;
            }

            return $this->handleResponse($response, $raw);
        }
    }

    /**
     * @return array<string,mixed>|string
     */
    protected function handleResponse(ResponseInterface $response, bool $raw)
    {
        $status = $response->getStatusCode();
        $contents = (string) $response->getBody();

        if ($raw && $status >= 200 && $status < 300) {
            return $contents;
        }

        $decoded = json_decode($contents, true);
        $body = is_array($decoded) ? $decoded : [];

        $isErrorStatus = $status < 200 || $status >= 300;
        $isErrorEnvelope = (isset($body['status']) && $body['status'] === 'error')
            || (array_key_exists('success', $body) && $body['success'] === false);

        if ($isErrorStatus || $isErrorEnvelope) {
            $errorCode = isset($body['code']) ? (string) $body['code'] : null;
            $message = isset($body['message']) && is_string($body['message'])
                ? $body['message']
                : ('HiTechCloud trả về lỗi HTTP ' . $status);

            throw new ApiException($message, $status, $errorCode, $body);
        }

        if ($raw) {
            return $contents;
        }

        return $body;
    }

    protected function backoff(int $attempt, ?ResponseInterface $response = null): void
    {
        $seconds = null;

        if ($response !== null && $response->hasHeader('Retry-After')) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            if (is_numeric($retryAfter)) {
                $seconds = (float) $retryAfter;
            }
        }

        if ($seconds === null) {
            $seconds = 0.5 * (2 ** ($attempt - 1));
        }

        $seconds = min($seconds, 10.0);

        usleep((int) ($seconds * 1_000_000));
    }

    protected function generateIdempotencyKey(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
