<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Webhook;

/**
 * Bóc tách và truy cập payload webhook một cách thuận tiện.
 *
 * Hỗ trợ cả payload biến động số dư (event, transferType, transferAmount, code...),
 * IPN cổng thanh toán (notification_type, order...) và các payload khác.
 */
class WebhookPayload
{
    /** @var array<string,mixed> */
    protected array $data;

    protected string $raw;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data, string $raw = '')
    {
        $this->data = $data;
        $this->raw = $raw;
    }

    public static function fromJson(string $rawBody): self
    {
        $decoded = json_decode($rawBody, true);

        return new self(is_array($decoded) ? $decoded : [], $rawBody);
    }

    /**
     * Tên sự kiện. Ưu tiên `event`, fallback sang `notification_type`, hoặc
     * suy ra từ `transferType` (transaction.in / transaction.out).
     */
    public function event(): ?string
    {
        if (isset($this->data['event'])) {
            return (string) $this->data['event'];
        }

        if (isset($this->data['notification_type'])) {
            return (string) $this->data['notification_type'];
        }

        if (isset($this->data['transferType'])) {
            return 'transaction.' . $this->data['transferType'];
        }

        return null;
    }

    public function is(string $event): bool
    {
        return $this->event() === $event;
    }

    public function isIncoming(): bool
    {
        return $this->is(WebhookEvents::TRANSACTION_IN);
    }

    public function isPing(): bool
    {
        return $this->is(WebhookEvents::WEBHOOK_PING);
    }

    /**
     * Mã bóc tách (code) trên nội dung chuyển khoản, dùng để khớp đơn hàng.
     */
    public function code(): ?string
    {
        return $this->data['code'] ?? null;
    }

    /**
     * Số tiền giao dịch (biến động số dư).
     */
    public function amount(): ?int
    {
        if (isset($this->data['transferAmount'])) {
            return (int) $this->data['transferAmount'];
        }

        return null;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function raw(): string
    {
        return $this->raw;
    }
}
