<?php

declare(strict_types=1);

use HiTechCloud\Http\Controllers\WebhookController;
use HiTechCloud\Pay\Webhook\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

/**
 * Route nhận webhook cho các module HiTechCloud.
 *
 * File này được nạp bởi HiTechCloudPayServiceProvider khi
 * `config('hitechcloudpay.webhooks.enabled')` bật. Tiền tố và middleware nhóm
 * lấy từ config; mỗi route còn gắn thêm middleware xác minh chữ ký theo loại.
 *
 * Mỗi route đặt sẵn tham số `product` và loại chữ ký tương ứng:
 *   POST {prefix}/pay       transaction (biến động số dư) – X-HiTechCloud-Signature
 *   POST {prefix}/gateway   IPN cổng thanh toán           – X-HiTechCloud-Signature
 *   POST {prefix}/bankhub   BankHub                       – X-HiTechCloud-BankHub-Signature
 *   POST {prefix}/einvoice  Hoá đơn điện tử               – X-HTC-Signature
 *   POST {prefix}/omni      Omnichannel                   – X-HTC-Signature
 *   POST {prefix}/crm       CRM                           – X-CRM-Signature
 *   POST {prefix}/sale-ai   Sale AI                       – X-SaleAI-Signature
 *   POST {prefix}/pos       POS                           – X-HitechPos-Signature
 */
$prefix = (string) config('hitechcloudpay.webhooks.prefix', 'hitechcloud/webhooks');
$groupMiddleware = (array) config('hitechcloudpay.webhooks.middleware', ['api']);
$verify = VerifyWebhookSignature::class;

Route::group(['prefix' => $prefix, 'middleware' => $groupMiddleware], function () use ($verify) {
    $map = [
        'pay' => ['product' => 'pay', 'type' => 'transaction'],
        'gateway' => ['product' => 'pay', 'type' => 'gateway'],
        'bankhub' => ['product' => 'pay', 'type' => 'bankhub'],
        'einvoice' => ['product' => 'pay', 'type' => 'einvoice'],
        'omni' => ['product' => 'omni', 'type' => 'omni'],
        'crm' => ['product' => 'crm', 'type' => 'crm'],
        'sale-ai' => ['product' => 'saleai', 'type' => 'saleai'],
        'pos' => ['product' => 'pos', 'type' => 'pos'],
    ];

    foreach ($map as $uri => $cfg) {
        Route::post($uri, [WebhookController::class, 'handle'])
            ->defaults('product', $cfg['product'])
            ->middleware($verify . ':' . $cfg['type'])
            ->name('hitechcloud.webhooks.' . str_replace('-', '_', $uri));
    }
});
