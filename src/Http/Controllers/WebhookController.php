<?php

declare(strict_types=1);

namespace HiTechCloud\Http\Controllers;

use HiTechCloud\Events\BalanceChanged;
use HiTechCloud\Events\PaymentFailed;
use HiTechCloud\Events\PaymentSucceeded;
use HiTechCloud\Events\RefundCompleted;
use HiTechCloud\Events\WebhookReceived;
use HiTechCloud\Jobs\ProcessWebhookJob;
use HiTechCloud\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Controller nhận webhook cho mọi module (đứng sau middleware
 * {@see \HiTechCloud\Pay\Webhook\VerifyWebhookSignature} đã xác minh chữ ký).
 *
 * Quy trình:
 *  1. Bóc tách payload JSON.
 *  2. Lưu bản ghi {@see WebhookEvent} (dedup theo `event_id`) nếu bật persist.
 *  3. Phát {@see WebhookReceived} + event nghiệp vụ tương ứng (Payment*, Refund*,
 *     BalanceChanged...).
 *  4. Đẩy {@see ProcessWebhookJob} vào hàng đợi.
 *  5. Trả 200 để nhà cung cấp không gửi lại.
 */
class WebhookController
{
    public function handle(Request $request, string $product = 'pay'): JsonResponse
    {
        $raw = $request->getContent();
        $decoded = json_decode($raw, true);
        $payload = is_array($decoded) ? $decoded : [];

        $event = $this->resolveEvent($payload, $request);
        $eventId = $this->resolveEventId($payload, $request);

        $record = null;

        if ((bool) config('hitechcloudpay.database.persist', true)) {
            try {
                $record = $this->persist($product, $event, $eventId, $payload);
            } catch (Throwable $e) {
                report($e);
            }
        }

        event(new WebhookReceived($product, $event, $payload, $record));
        $this->dispatchBusinessEvent($event, $payload);

        ProcessWebhookJob::dispatch(
            $record !== null ? $record->getKey() : null,
            $product,
            $event,
            $payload
        );

        return response()->json(['success' => true]);
    }

    /**
     * Lưu (hoặc bỏ qua nếu trùng) một bản ghi webhook.
     *
     * @param array<string,mixed> $payload
     */
    protected function persist(string $product, ?string $event, ?string $eventId, array $payload): WebhookEvent
    {
        if ($eventId !== null && $eventId !== '') {
            $existing = WebhookEvent::query()->where('event_id', $eventId)->first();
            if ($existing !== null) {
                return $existing;
            }
        }

        return WebhookEvent::query()->create([
            'product' => $product,
            'event' => $event,
            'event_id' => $eventId,
            'payload' => $payload,
            'signature_valid' => true,
            'received_at' => now(),
        ]);
    }

    /**
     * @param array<string,mixed> $payload
     */
    protected function dispatchBusinessEvent(?string $event, array $payload): void
    {
        if ($event === null) {
            return;
        }

        $reference = $payload['code']
            ?? ($payload['order_invoice_number'] ?? ($payload['reference_code'] ?? null));
        $reference = $reference !== null ? (string) $reference : null;

        $amount = null;
        foreach (['transferAmount', 'amount', 'order_amount', 'amount_in'] as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                $amount = (int) $payload[$key];
                break;
            }
        }

        switch ($event) {
            case 'transaction.in':
            case 'transaction.out':
                event(new BalanceChanged(
                    $payload,
                    (string) ($payload['transferType'] ?? ($event === 'transaction.in' ? 'in' : 'out')),
                    $amount,
                    $reference
                ));
                break;

            case 'ORDER_CAPTURED':
            case 'payment.succeeded':
                event(new PaymentSucceeded($payload, $reference, $amount));
                break;

            case 'ORDER_FAILED':
            case 'payment.failed':
                event(new PaymentFailed($payload, $reference, isset($payload['reason']) ? (string) $payload['reason'] : null));
                break;

            case 'ORDER_REFUNDED':
            case 'transaction.reversed':
                event(new RefundCompleted($payload, $reference, $amount));
                break;
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    protected function resolveEvent(array $payload, Request $request): ?string
    {
        foreach (['event', 'event_type', 'notification_type', 'type'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                return $payload[$key];
            }
        }

        foreach (['X-HitechPos-Event', 'X-HTC-Event', 'X-Event'] as $header) {
            $value = $request->header($header);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        if (isset($payload['transferType'])) {
            return 'transaction.' . $payload['transferType'];
        }

        return null;
    }

    /**
     * @param array<string,mixed> $payload
     */
    protected function resolveEventId(array $payload, Request $request): ?string
    {
        foreach (['event_id', 'eventId', 'id', 'reference_number', 'referenceCode'] as $key) {
            if (isset($payload[$key]) && (is_string($payload[$key]) || is_numeric($payload[$key]))) {
                return (string) $payload[$key];
            }
        }

        foreach (['X-HitechPos-Delivery', 'X-HTC-Delivery', 'X-Delivery-Id'] as $header) {
            $value = $request->header($header);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
