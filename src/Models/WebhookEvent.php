<?php

declare(strict_types=1);

namespace HiTechCloud\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bản ghi một sự kiện webhook đã nhận (dùng chung cho mọi module emit webhook:
 * Pay, Omni, CRM, Sale AI, POS...).
 *
 * Bảng: `htc_pay_webhook_events` (tiền tố bảng lấy từ config `hitechcloudpay.database`).
 * Dedup theo cột `event_id` (unique).
 *
 * @property int         $id
 * @property string      $product
 * @property string|null $event
 * @property string|null $event_id
 * @property array       $payload
 * @property bool        $signature_valid
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property \Illuminate\Support\Carbon|null $processed_at
 */
class WebhookEvent extends Model
{
    /** @var array<int,string> */
    protected $guarded = [];

    /** @var array<string,string> */
    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function getTable()
    {
        return $this->prefix() . 'htc_pay_webhook_events';
    }

    public function getConnectionName()
    {
        return config('hitechcloudpay.database.connection') ?: parent::getConnectionName();
    }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    protected function prefix(): string
    {
        return (string) config('hitechcloudpay.database.table_prefix', '');
    }
}
