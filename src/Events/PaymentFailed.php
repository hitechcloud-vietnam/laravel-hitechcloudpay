<?php

declare(strict_types=1);

namespace HiTechCloud\Events;

/**
 * Phát ra khi một khoản thanh toán thất bại / bị từ chối.
 */
class PaymentFailed
{
    /** @var array<string,mixed> */
    public array $payload;

    public ?string $reference;

    public ?string $reason;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(array $payload, ?string $reference = null, ?string $reason = null)
    {
        $this->payload = $payload;
        $this->reference = $reference;
        $this->reason = $reason;
    }
}
