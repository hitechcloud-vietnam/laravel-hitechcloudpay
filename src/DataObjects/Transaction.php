<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\DataObjects;

/**
 * Giá trị đại diện cho một giao dịch biến động số dư (Pay API v2).
 *
 * Bọc mảng dữ liệu thô trả về từ API để truy cập thuận tiện, vẫn giữ nguyên
 * mảng gốc qua {@see toArray()} để không mất trường nào.
 */
class Transaction
{
    /** @var array<string,mixed> */
    protected array $attributes;

    /**
     * @param array<string,mixed> $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param array<string,mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    public function id(): ?string
    {
        return isset($this->attributes['id']) ? (string) $this->attributes['id'] : null;
    }

    public function code(): ?string
    {
        return $this->attributes['code'] ?? null;
    }

    public function transferType(): ?string
    {
        return $this->attributes['transfer_type'] ?? ($this->attributes['transferType'] ?? null);
    }

    public function isIncoming(): bool
    {
        return $this->transferType() === 'in';
    }

    public function amountIn(): int
    {
        return (int) ($this->attributes['amount_in'] ?? ($this->attributes['transferAmount'] ?? 0));
    }

    public function amountOut(): int
    {
        return (int) ($this->attributes['amount_out'] ?? 0);
    }

    public function content(): ?string
    {
        return $this->attributes['transaction_content'] ?? ($this->attributes['content'] ?? null);
    }

    public function referenceNumber(): ?string
    {
        return $this->attributes['reference_number'] ?? ($this->attributes['referenceCode'] ?? null);
    }

    /**
     * Truy cập một thuộc tính bất kỳ.
     *
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
