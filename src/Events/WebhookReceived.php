<?php

declare(strict_types=1);

namespace HiTechCloud\Events;

use HiTechCloud\Models\WebhookEvent;

/**
 * Phát ra ngay sau khi một webhook hợp lệ được nhận và lưu lại.
 *
 * Lắng nghe sự kiện này để xử lý nghiệp vụ tuỳ ý. Payload đã được xác minh chữ
 * ký (nếu bật) và bóc tách thành mảng.
 */
class WebhookReceived
{
    public string $product;

    public ?string $event;

    /** @var array<string,mixed> */
    public array $payload;

    public ?WebhookEvent $record;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(string $product, ?string $event, array $payload, ?WebhookEvent $record = null)
    {
        $this->product = $product;
        $this->event = $event;
        $this->payload = $payload;
        $this->record = $record;
    }
}
