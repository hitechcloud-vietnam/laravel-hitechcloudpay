<?php

declare(strict_types=1);

namespace HiTechCloud\Jobs;

use HiTechCloud\Models\WebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job hàng đợi xử lý một webhook đã lưu.
 *
 * Mặc định chỉ đánh dấu `processed_at`. Ứng dụng có thể lắng nghe
 * {@see \HiTechCloud\Events\WebhookReceived} để chạy nghiệp vụ riêng, hoặc mở
 * rộng job này.
 */
class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int|string|null Khoá chính của bản ghi webhook (nếu đã lưu DB). */
    public $webhookEventId;

    public string $product;

    public ?string $event;

    /** @var array<string,mixed> */
    public array $payload;

    /**
     * @param int|string|null     $webhookEventId
     * @param array<string,mixed> $payload
     */
    public function __construct($webhookEventId, string $product, ?string $event, array $payload)
    {
        $this->webhookEventId = $webhookEventId;
        $this->product = $product;
        $this->event = $event;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        if ($this->webhookEventId === null) {
            return;
        }

        $record = WebhookEvent::query()->find($this->webhookEventId);

        if ($record !== null && $record->processed_at === null) {
            $record->processed_at = now();
            $record->save();
        }
    }
}
