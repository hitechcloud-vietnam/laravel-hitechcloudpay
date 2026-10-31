<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tạo các bảng dùng cho HiTechCloud SDK:
 *  - htc_pay_webhook_events : nhật ký webhook đã nhận (dedup theo event_id).
 *  - htc_pay_transactions   : giao dịch thanh toán lưu lại để đối soát.
 *  - htc_pay_sessions       : phiên thanh toán / checkout đã tạo.
 *
 * Dùng anonymous-class migration để tương thích Laravel 9–13. Tiền tố bảng và
 * connection lấy từ config `hitechcloudpay.database`.
 */
return new class extends Migration
{
    public function up(): void
    {
        $schema = $this->schema();
        $prefix = $this->prefix();

        $schema->create($prefix . 'htc_pay_webhook_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product', 32)->index();
            $table->string('event', 64)->nullable()->index();
            $table->string('event_id', 191)->nullable()->unique();
            $table->json('payload')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        $schema->create($prefix . 'htc_pay_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference', 191)->nullable()->index();
            $table->string('status', 48)->nullable()->index();
            $table->bigInteger('amount')->nullable();
            $table->string('currency', 8)->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });

        $schema->create($prefix . 'htc_pay_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session', 191)->nullable()->index();
            $table->string('status', 48)->nullable()->index();
            $table->bigInteger('amount')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $schema = $this->schema();
        $prefix = $this->prefix();

        $schema->dropIfExists($prefix . 'htc_pay_sessions');
        $schema->dropIfExists($prefix . 'htc_pay_transactions');
        $schema->dropIfExists($prefix . 'htc_pay_webhook_events');
    }

    private function schema(): \Illuminate\Database\Schema\Builder
    {
        $connection = config('hitechcloudpay.database.connection');

        return $connection ? Schema::connection($connection) : Schema::connection(null);
    }

    private function prefix(): string
    {
        return (string) config('hitechcloudpay.database.table_prefix', '');
    }
};
