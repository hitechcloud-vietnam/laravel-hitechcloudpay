<?php

declare(strict_types=1);

namespace HiTechCloud\Events;

/**
 * Phát ra khi có biến động số dư (webhook transaction.in / transaction.out).
 */
class BalanceChanged
{
    /** @var array<string,mixed> */
    public array $payload;

    public ?string $transferType;

    public ?int $amount;

    public ?string $code;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(array $payload, ?string $transferType = null, ?int $amount = null, ?string $code = null)
    {
        $this->payload = $payload;
        $this->transferType = $transferType;
        $this->amount = $amount;
        $this->code = $code;
    }
}
