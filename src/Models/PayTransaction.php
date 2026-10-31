<?php

declare(strict_types=1);

namespace HiTechCloud\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bản ghi một giao dịch thanh toán được lưu lại (đối soát, tra cứu nhanh).
 *
 * Bảng: `htc_pay_transactions`.
 *
 * @property int         $id
 * @property string|null $reference
 * @property string|null $status
 * @property int|null    $amount
 * @property string|null $currency
 * @property array       $raw
 */
class PayTransaction extends Model
{
    /** @var array<int,string> */
    protected $guarded = [];

    /** @var array<string,string> */
    protected $casts = [
        'raw' => 'array',
        'amount' => 'integer',
    ];

    public function getTable()
    {
        return $this->prefix() . 'htc_pay_transactions';
    }

    public function getConnectionName()
    {
        return config('hitechcloudpay.database.connection') ?: parent::getConnectionName();
    }

    protected function prefix(): string
    {
        return (string) config('hitechcloudpay.database.table_prefix', '');
    }
}
