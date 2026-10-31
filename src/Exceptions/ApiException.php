<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Exceptions;

use Throwable;

/**
 * Ném ra khi API HiTechCloud Pay trả về lỗi (HTTP >= 400) hoặc phản hồi
 * có `status: error`.
 *
 * Giữ lại mã HTTP, mã lỗi dạng chuỗi (ví dụ UNAUTHORIZED, VALIDATION_ERROR),
 * thông điệp và toàn bộ body phản hồi để phục vụ gỡ lỗi / đối soát.
 */
class ApiException extends HiTechCloudPayException
{
    protected int $statusCode;

    /** @var string|null Mã lỗi dạng chuỗi do API trả về (trường `code`). */
    protected ?string $errorCode;

    /** @var array<string,mixed> Toàn bộ body phản hồi đã giải mã (nếu có). */
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

    /**
     * Có phải lỗi do vượt giới hạn tần suất (429 / TOO_MANY_REQUESTS) không.
     */
    public function isRateLimited(): bool
    {
        return $this->statusCode === 429 || $this->errorCode === 'TOO_MANY_REQUESTS';
    }

    /**
     * Có phải lỗi xác thực (401 / UNAUTHORIZED) không.
     */
    public function isUnauthorized(): bool
    {
        return $this->statusCode === 401 || $this->errorCode === 'UNAUTHORIZED';
    }
}
