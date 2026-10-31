<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Webhook;

/**
 * Hằng số tên sự kiện webhook theo tài liệu HiTechCloud Pay.
 *
 * (Dùng hằng số thay vì enum để tương thích PHP 8.0.)
 */
final class WebhookEvents
{
    /* ---- Biến động số dư (Pay API v2) ---- */
    public const TRANSACTION_IN = 'transaction.in';
    public const TRANSACTION_OUT = 'transaction.out';
    public const TRANSACTION_REVERSED = 'transaction.reversed';
    public const WEBHOOK_PING = 'webhook.ping';

    /* ---- Cổng thanh toán (IPN notification_type) ---- */
    public const ORDER_CAPTURED = 'ORDER_CAPTURED';
    public const ORDER_REFUNDED = 'ORDER_REFUNDED';

    /* ---- BankHub ---- */
    public const BANKHUB_TRANSACTION_CREATED = 'TRANSACTION_CREATED';

    /* ---- eInvoice ---- */
    public const INVOICE_APPROVED = 'invoice.approved';
    public const INVOICE_REJECTED = 'invoice.rejected';

    /**
     * Danh sách sự kiện biến động số dư.
     *
     * @return array<int,string>
     */
    public static function transactionEvents(): array
    {
        return [
            self::TRANSACTION_IN,
            self::TRANSACTION_OUT,
            self::TRANSACTION_REVERSED,
        ];
    }
}
