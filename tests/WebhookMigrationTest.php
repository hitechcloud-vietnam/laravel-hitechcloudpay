<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Tests;

use HiTechCloud\Models\PayTransaction;
use HiTechCloud\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

/**
 * Kiểm thử migration & model của SDK bằng SQLite in-memory (orchestra/testbench).
 */
class WebhookMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tables_are_created(): void
    {
        $this->assertTrue(Schema::hasTable('htc_pay_webhook_events'));
        $this->assertTrue(Schema::hasTable('htc_pay_transactions'));
        $this->assertTrue(Schema::hasTable('htc_pay_sessions'));
    }

    public function test_webhook_event_persists_and_casts_json(): void
    {
        $event = WebhookEvent::create([
            'product' => 'pay',
            'event' => 'transaction.in',
            'event_id' => 'evt_1',
            'payload' => ['transferAmount' => 500000, 'code' => 'DH1'],
            'signature_valid' => true,
            'received_at' => now(),
        ]);

        $this->assertIsArray($event->fresh()->payload);
        $this->assertSame(500000, $event->fresh()->payload['transferAmount']);
        $this->assertTrue($event->fresh()->signature_valid);
        $this->assertFalse($event->fresh()->isProcessed());
    }

    public function test_pay_transaction_model_works(): void
    {
        $tx = PayTransaction::create([
            'reference' => 'INV-1',
            'status' => 'CAPTURED',
            'amount' => 250000,
            'currency' => 'VND',
            'raw' => ['order_status' => 'CAPTURED'],
        ]);

        $this->assertSame('CAPTURED', $tx->fresh()->status);
        $this->assertSame(250000, $tx->fresh()->amount);
        $this->assertSame('CAPTURED', $tx->fresh()->raw['order_status']);
    }
}
