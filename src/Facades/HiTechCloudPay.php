<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade cho client HiTechCloud Pay.
 *
 * @method static \HiTechCloud\Pay\Resources\Gateway gateway()
 * @method static \HiTechCloud\Pay\Resources\BankAccounts bankAccounts()
 * @method static \HiTechCloud\Pay\Resources\VirtualAccounts virtualAccounts()
 * @method static \HiTechCloud\Pay\Resources\Transactions transactions()
 * @method static \HiTechCloud\Pay\Resources\Balance balance()
 * @method static \HiTechCloud\Pay\Resources\Speaker speaker()
 * @method static \HiTechCloud\Pay\Resources\EInvoice eInvoice()
 * @method static \HiTechCloud\Pay\Resources\BankHub bankHub()
 * @method static \HiTechCloud\Pay\Resources\Webhooks webhooks()
 * @method static \HiTechCloud\Pay\Client\HttpClient http()
 * @method static array config()
 *
 * @see \HiTechCloud\Pay\HiTechCloudPay
 */
class HiTechCloudPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hitechcloudpay';
    }
}
