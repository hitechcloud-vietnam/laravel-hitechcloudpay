<?php

declare(strict_types=1);

namespace HiTechCloud\Exceptions;

use Throwable;

/**
 * Ném ra khi một API HiTechCloud trả về lỗi (HTTP >= 400) hoặc envelope có
 * `status: error` / `success: false`.
 *
 * Giữ lại mã HTTP, mã lỗi dạng chuỗi, thông điệp và toàn bộ body phản hồi để
 * phục vụ gỡ lỗi và đối soát.
 */
class ApiException extends HiTechCloudException
{
    protected int $statusCode;

    protected ?string $errorCode;

    /** @var array<string,mixed> */
    protected array $body;

    /**
     * @param array<string,mixed> $body
     */
    public function __construct(
        string $message,
        int $statusCode = 0,
        ?string $errorCode = null,
        array $body = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);

        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @return array<string,mixed>
     */
    public function getBody(): array
    {
        return $this->body;
    }

    public function isRateLimited(): bool
    {
        return $this->statusCode === 429 || $this->errorCode === 'TOO_MANY_REQUESTS';
    }

    public function isUnauthorized(): bool
    {
        return $this->statusCode === 401 || $this->errorCode === 'UNAUTHORIZED';
    }
}
