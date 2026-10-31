<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cấu hình HiTechCloud Pay
|--------------------------------------------------------------------------
|
| Mỗi sản phẩm của HiTechCloud Pay chạy trên một host riêng (xem tài liệu
| tại https://developers.hitechcloud.vn). Khi bật `sandbox`, các host sẽ tự
| chuyển sang môi trường thử nghiệm tương ứng.
|
| - Cổng thanh toán (Gateway)  : xác thực Basic (merchant_id:secret_key)
| - Pay API v2 (Core)          : xác thực Bearer access token
| - BankHub / Speaker / eInvoice: đổi client_id/client_secret lấy Bearer token
|
*/

$sandbox = (bool) env('HITECHCLOUDPAY_SANDBOX', false);

return [

    /*
    |--------------------------------------------------------------------------
    | Chế độ Sandbox
    |--------------------------------------------------------------------------
    */
    'sandbox' => $sandbox,

    /*
    |--------------------------------------------------------------------------
    | Tuỳ chọn HTTP
    |--------------------------------------------------------------------------
    | timeout : thời gian chờ tối đa (giây) cho mỗi request.
    | retries : số lần thử lại khi gặp lỗi 429 / 5xx.
    */
    'timeout' => (int) env('HITECHCLOUDPAY_TIMEOUT', 15),
    'retries' => (int) env('HITECHCLOUDPAY_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | URL nền tảng (landing / dashboard)
    |--------------------------------------------------------------------------
    */
    'base_url' => env('HITECHCLOUDPAY_BASE_URL', 'https://pay.hitechcloud.vn'),

    /*
    |--------------------------------------------------------------------------
    | Webhook biến động số dư (Pay API v2)
    |--------------------------------------------------------------------------
    | Dùng để xác minh chữ ký HMAC-SHA256 ở header X-HiTechCloud-Signature.
    | tolerance: dung sai timestamp (giây) để chống tấn công phát lại.
    */
    'webhook_secret' => env('HITECHCLOUDPAY_WEBHOOK_SECRET'),
    'webhook_tolerance' => (int) env('HITECHCLOUDPAY_WEBHOOK_TOLERANCE', 300),

    /*
    |--------------------------------------------------------------------------
    | Cổng thanh toán (Payment Gateway)
    |--------------------------------------------------------------------------
    | Checkout là form submit từ trình duyệt tới `checkout_url`; REST API tra
    | cứu / hoàn tiền / đối soát dùng `api_url` với Basic Auth.
    */
    'gateway' => [
        'merchant_id' => env('HITECHCLOUDPAY_MERCHANT_ID'),
        'secret_key' => env('HITECHCLOUDPAY_SECRET_KEY'),
        'api_url' => $sandbox
            ? env('HITECHCLOUDPAY_GATEWAY_API_URL', 'https://pgapi-sandbox.hitechcloud.vn')
            : env('HITECHCLOUDPAY_GATEWAY_API_URL', 'https://pgapi.hitechcloud.vn'),
        'checkout_url' => $sandbox
            ? env('HITECHCLOUDPAY_GATEWAY_CHECKOUT_URL', 'https://pay-sandbox.hitechcloud.vn')
            : env('HITECHCLOUDPAY_GATEWAY_CHECKOUT_URL', 'https://pay-gateways.hitechcloud.vn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pay API v2 (Core) - userapi.hitechcloud.vn
    |--------------------------------------------------------------------------
    | Xác thực bằng access token (Bearer) cấp sẵn trong dashboard.
    */
    'core' => [
        'token' => env('HITECHCLOUDPAY_API_TOKEN'),
        'base_url' => env('HITECHCLOUDPAY_CORE_URL', 'https://userapi.hitechcloud.vn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BankHub (Open Banking) - bankhub-api.hitechcloud.vn
    |--------------------------------------------------------------------------
    */
    'bankhub' => [
        'client_id' => env('HITECHCLOUDPAY_BANKHUB_CLIENT_ID'),
        'client_secret' => env('HITECHCLOUDPAY_BANKHUB_CLIENT_SECRET'),
        'company_xid' => env('HITECHCLOUDPAY_BANKHUB_COMPANY_XID'),
        'base_url' => env('HITECHCLOUDPAY_BANKHUB_URL', 'https://bankhub-api.hitechcloud.vn'),
        'webhook_secret' => env('HITECHCLOUDPAY_BANKHUB_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Loa thông báo SoundBox (Speaker) - speaker-api.hitechcloud.vn
    |--------------------------------------------------------------------------
    */
    'speaker' => [
        'client_id' => env('HITECHCLOUDPAY_SPEAKER_CLIENT_ID'),
        'client_secret' => env('HITECHCLOUDPAY_SPEAKER_CLIENT_SECRET'),
        'base_url' => $sandbox
            ? env('HITECHCLOUDPAY_SPEAKER_URL', 'https://sandbox.speaker-api.hitechcloud.vn')
            : env('HITECHCLOUDPAY_SPEAKER_URL', 'https://speaker-api.hitechcloud.vn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hoá đơn điện tử (eInvoice) - einvoice-api.hitechcloud.vn
    |--------------------------------------------------------------------------
    */
    'einvoice' => [
        'client_id' => env('HITECHCLOUDPAY_EINVOICE_CLIENT_ID'),
        'client_secret' => env('HITECHCLOUDPAY_EINVOICE_CLIENT_SECRET'),
        'provider_account_id' => env('HITECHCLOUDPAY_EINVOICE_PROVIDER_ACCOUNT_ID'),
        'base_url' => env('HITECHCLOUDPAY_EINVOICE_URL', 'https://einvoice-api.hitechcloud.vn'),
        'webhook_secret' => env('HITECHCLOUDPAY_EINVOICE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quản trị Webhook (Admin API) - my.hitechcloud.vn
    |--------------------------------------------------------------------------
    | Dùng để đăng ký endpoint nhận webhook và giả lập sự kiện. Xác thực bằng
    | API key dạng header `Authorization: Apikey <KEY>`.
    */
    'webhooks' => [
        'admin_api_key' => env('HITECHCLOUDPAY_ADMIN_API_KEY'),
        'admin_url' => $sandbox
            ? env('HITECHCLOUDPAY_ADMIN_URL', 'https://sandbox.my.hitechcloud.vn')
            : env('HITECHCLOUDPAY_ADMIN_URL', 'https://my.hitechcloud.vn'),
    ],

];
