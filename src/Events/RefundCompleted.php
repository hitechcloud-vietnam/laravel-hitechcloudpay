<?php

declare(strict_types=1);

namespace HiTechCloud\Events;

/**
 * Phát ra khi một yêu cầu hoàn tiền hoàn tất (ORDER_REFUNDED / transaction.reversed).
 */
class RefundCompleted
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
