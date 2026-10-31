<?php

declare(strict_types=1);

namespace HiTechCloud\Events;

/**
 * Phát ra khi một khoản thanh toán thành công (đơn CAPTURED hoặc tiền vào khớp
 * đơn). Mang theo payload webhook gốc để xử lý nghiệp vụ.
 */
class PaymentSucceeded
{
    /** @var array<string,mixed> */
    public array $payload;

    public ?string $reference;

    public ?int $amount;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(array $payload, ?string $reference = null, ?int $amount = null)
    {
        $this->payload = $payload;
        $this->reference = $reference;
        $this->amount = $amount;
    }
}
