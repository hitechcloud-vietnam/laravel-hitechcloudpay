# HiTechCloud - SDK cho Laravel

[![Latest Version](https://img.shields.io/packagist/v/hitechcloud-vietnam/laravel-hitechcloudpay.svg)](https://packagist.org/packages/hitechcloud-vietnam/laravel-hitechcloudpay)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

SDK Laravel chính thức cho toàn bộ hệ sinh thái **HiTechCloud** - một gói duy nhất cho nhiều sản phẩm:

- **Pay** - cổng thanh toán (QR VietQR/NAPAS, thẻ, ví), Pay API v2 (tài khoản ngân hàng, VA, giao dịch), webhook biến động số dư, loa SoundBox, hoá đơn điện tử, Open Banking (BankHub).
- **eKYC / VNeID** - cổng đại lý C06 (ekyc-c06.hitechcloud.vn, ký RSA/JWS).
- **POS** - HiTechPos (pos.hitechcloud.vn).
- **Omnichannel** - hội thoại đa kênh (omni.hitechcloud.vn).
- **CRM** - quản lý quan hệ khách hàng (crm-gateways.hitechcloud.vn).
- **WAF** - tường lửa ứng dụng web VCWAF (vcwaf.hitechcloud.vn).
- **Sale AI** - bot bán hàng AI (sale-ai.hitechcloud.vn).
- **API Tools** - bộ công cụ tra cứu (api-tools.hitechcloud.vn).
- **User API** - API khách hàng (api.hitechcloud.vn).

Mỗi sản phẩm nằm trong một namespace riêng dưới `HiTechCloud\` và được truy cập qua manager/facade hợp nhất `HiTechCloud`, hoặc trực tiếp (ví dụ facade `HiTechCloudPay` cho module Pay).

Tài liệu API gốc: <https://developers.hitechcloud.vn>

## Mục lục

- [Giới thiệu](#giới-thiệu)
- [Yêu cầu](#yêu-cầu)
- [Cài đặt](#cài-đặt)
- [Cấu hình `.env`](#cấu-hình-env)
- [Sử dụng](#sử-dụng)
  - [Cổng thanh toán](#1-cổng-thanh-toán-gateway)
  - [Tài khoản ngân hàng](#2-tài-khoản-ngân-hàng-pay-api-v2)
  - [Tài khoản ảo (VA)](#3-tài-khoản-ảo-virtual-account)
  - [Giao dịch](#4-giao-dịch)
  - [Số dư](#5-số-dư)
  - [Loa SoundBox](#6-loa-thông-báo-soundbox)
  - [Hoá đơn điện tử](#7-hoá-đơn-điện-tử-einvoice)
  - [Open Banking (BankHub)](#8-open-banking-bankhub)
- [Xử lý webhook](#xử-lý-webhook)
- [Sandbox vs Production](#sandbox-vs-production)
- [Xử lý lỗi / Exception](#xử-lý-lỗi--exception)
- [Giấy phép](#giấy-phép)

## Giới thiệu

Gói này cung cấp một client thuần PHP, có Facade tiện dụng, bao trọn các endpoint thực tế của HiTechCloud Pay. Mỗi nhóm sản phẩm là một "resource" riêng, mỗi phương thức ánh xạ tới đúng một endpoint trong tài liệu.

## Yêu cầu

- PHP **8.0+** (chạy tốt trên 8.0 – 8.4)
- Laravel **9, 10, 11, 12 hoặc 13**
- `guzzlehttp/guzzle ^7.4`

## Cài đặt

```bash
composer require hitechcloud-vietnam/laravel-hitechcloudpay
```

Gói hỗ trợ **auto-discovery**. Nếu cần, bạn có thể publish file config:

```bash
php artisan vendor:publish --tag=hitechcloudpay-config
```

File `config/hitechcloudpay.php` sẽ được tạo ra (chứa cấu hình cho tất cả module).

### Migrations

SDK đi kèm migration tạo 3 bảng: `htc_pay_webhook_events` (nhật ký webhook, dedup theo `event_id`), `htc_pay_transactions` và `htc_pay_sessions`. Migration được **tự động nạp** khi chạy `migrate`. Nếu muốn chỉnh sửa, hãy publish ra ứng dụng:

```bash
php artisan vendor:publish --tag=hitechcloud-migrations
php artisan migrate
```

Có thể đổi connection / tiền tố bảng / tắt lưu DB qua cấu hình `hitechcloudpay.database` (xem `.env` bên dưới). Đặt `HITECHCLOUDPAY_DB_PERSIST=false` nếu không muốn lưu webhook vào DB.

## Cấu hình `.env`

Chỉ cần khai báo thông tin của những sản phẩm bạn dùng:

```dotenv
# Chế độ sandbox (tự chuyển sang host thử nghiệm)
HITECHCLOUDPAY_SANDBOX=false

# --- Cổng thanh toán (Gateway) ---
HITECHCLOUDPAY_MERCHANT_ID=HTC_MCH_123456
HITECHCLOUDPAY_SECRET_KEY=your_secret_key

# --- Pay API v2 (Core - userapi.hitechcloud.vn) ---
HITECHCLOUDPAY_API_TOKEN=your_bearer_token

# --- Webhook biến động số dư ---
HITECHCLOUDPAY_WEBHOOK_SECRET=your_webhook_secret

# --- BankHub (Open Banking) ---
HITECHCLOUDPAY_BANKHUB_CLIENT_ID=
HITECHCLOUDPAY_BANKHUB_CLIENT_SECRET=
HITECHCLOUDPAY_BANKHUB_COMPANY_XID=
HITECHCLOUDPAY_BANKHUB_WEBHOOK_SECRET=

# --- Loa SoundBox ---
HITECHCLOUDPAY_SPEAKER_CLIENT_ID=
HITECHCLOUDPAY_SPEAKER_CLIENT_SECRET=

# --- Hoá đơn điện tử ---
HITECHCLOUDPAY_EINVOICE_CLIENT_ID=
HITECHCLOUDPAY_EINVOICE_CLIENT_SECRET=
HITECHCLOUDPAY_EINVOICE_PROVIDER_ACCOUNT_ID=
HITECHCLOUDPAY_EINVOICE_WEBHOOK_SECRET=

# --- Quản trị webhook (Admin API) ---
HITECHCLOUDPAY_ADMIN_API_KEY=
```

## Sử dụng

Dùng Facade `HiTechCloudPay`, hoặc inject `HiTechCloud\Pay\HiTechCloudPay` qua DI.

```php
use HiTechCloud\Pay\Facades\HiTechCloudPay;
```

### 1. Cổng thanh toán (Gateway)

**Tạo phiên thanh toán** (QR VietQR/NAPAS, thẻ, ví...). Checkout là form POST từ trình duyệt khách hàng; SDK dựng sẵn tham số đã ký HMAC-SHA256.

```php
$session = HiTechCloudPay::gateway()->createSession([
    'order_invoice_number' => 'INV-' . time(),
    'order_amount'         => 250000,                 // VNĐ, số nguyên
    'order_description'    => 'Thanh toan don hang #1234',
    'success_url'          => 'https://your-site.vn/payment/success',
    'error_url'            => 'https://your-site.vn/payment/error',
    'cancel_url'           => 'https://your-site.vn/payment/cancel',
    // tuỳ chọn: customer_name, customer_email, customer_phone, customer_id
]);

// Cách A: trả về form HTML tự submit sang trang checkout
return response($session->toAutoSubmitForm());

// Cách B: tự dựng form với $session->checkoutUrl và $session->params
```

**Kiểm tra trạng thái đơn**:

```php
$order  = HiTechCloudPay::gateway()->getOrder('INV-1759134677');
$status = HiTechCloudPay::gateway()->status('INV-1759134677'); // ví dụ "CAPTURED"
```

**Hoàn tiền** (một phần hoặc toàn phần):

```php
// Hoàn một phần
HiTechCloudPay::gateway()->refund('INV-1759134677', 50000, 'Thiếu hàng', 'RF-20261031-0001');

// Hoàn toàn bộ (bỏ trống amount)
HiTechCloudPay::gateway()->refund('INV-1759134677');
```

**Đối soát / liệt kê đơn hàng**:

```php
$orders = HiTechCloudPay::gateway()->listOrders([
    'page' => 1, 'per_page' => 50,
    'from_date' => '2026-10-01', 'to_date' => '2026-10-31',
]);
```

### 2. Tài khoản ngân hàng (Pay API v2)

```php
$accounts = HiTechCloudPay::bankAccounts()->list(['page' => 1, 'per_page' => 20, 'active' => 1]);
$account  = HiTechCloudPay::bankAccounts()->get('f9e8d7c6-b5a4-3210-fedc-ba0987654321');
```

### 3. Tài khoản ảo (Virtual Account)

```php
$va = HiTechCloudPay::virtualAccounts()->create([
    'bank_code'              => 'VCB',
    'account_number_primary' => '9988776655',
    'sub_account_name'       => 'VA KHACH HANG NGUYEN VAN A',
    'sub_account_code'       => 'HTC_VA_99881',
    'max_amount'             => 50000000,
    'expired_at'             => '2026-11-30 23:59:59',
]);

$list   = HiTechCloudPay::virtualAccounts()->list(['status' => 'ACTIVE']);
$detail = HiTechCloudPay::virtualAccounts()->get($va['id']);
HiTechCloudPay::virtualAccounts()->delete($va['id']);
```

### 4. Giao dịch

```php
$txs = HiTechCloudPay::transactions()->list([
    'code' => 'DH100234',
    'account_number' => '9988776655',
    'transfer_type' => 'in',
    'from_date' => '2026-10-01', 'to_date' => '2026-10-31',
    'per_page' => 100,
]);

// Chi tiết (mảng) hoặc DTO
$tx  = HiTechCloudPay::transactions()->get('a1b2c3d4-e5f6-7890-abcd-ef1234567890');
$dto = HiTechCloudPay::transactions()->find('a1b2c3d4-e5f6-7890-abcd-ef1234567890');
$dto->amountIn();     // 1500000
$dto->isIncoming();   // true
```

### 5. Số dư

Số dư khả dụng tức thời được lấy qua BankHub Open Banking:

```php
$balance = HiTechCloudPay::balance()->get('acc_8899aabb');
// $balance['current_balance']
```

> Nên cache kết quả tối đa ~60 giây và ưu tiên nhận biến động số dư qua webhook.

### 6. Loa thông báo SoundBox

```php
$speaker = HiTechCloudPay::speaker();

// Ghép nối
$speaker->pairCheck('HTC_S5_2508000001');
$req = $speaker->pairRequest('HTC_S5_2508000001');     // loa đọc OTP 4 số, trả session_id
$speaker->pairVerify('HTC_S5_2508000001', $req['session_id'] ?? '', '8921');

// Bắn thông báo giao dịch (loa đọc số tiền)
$speaker->notify('HTC_S5_2508000001', 500000, 'TXN_20261031_777', [
    'voiceType' => 'SOUTHERN_FEMALE',
    'prefix'    => 'Cửa hàng ABC xin thông báo',
    'suffix'    => 'Cảm ơn quý khách',
]);

$speaker->checkNotify('TXN_20261031_777');
$speaker->listDevices();
$speaker->unpair('HTC_S5_2508000001');
```

### 7. Hoá đơn điện tử (eInvoice)

```php
$einvoice = HiTechCloudPay::eInvoice();

$issued = $einvoice->create([
    'template_code'  => '1',
    'invoice_series' => '1C26TSE',
    'reference_code' => 'INV-HTC-2026-8899',
    'payment_method' => 'TM/CK',
    'is_draft'       => false,
    'buyer' => [
        'legal_name' => 'CÔNG TY TNHH ABC',
        'tax_code'   => '0315998877',
        'email'      => 'hoadon@abc.vn',
        'address'    => 'Quận 1, TP Hồ Chí Minh',
    ],
    'items' => [[
        'name' => 'Thuê máy chủ', 'unit' => 'Tháng',
        'quantity' => 1, 'unit_price' => 5000000, 'tax_rate' => '10%', 'amount' => 5000000,
    ]],
]); // -> trả về tracking_code (bất đồng bộ)

$status = $einvoice->checkStatus($issued['tracking_code'] ?? '');
$detail = $einvoice->get('INV-HTC-2026-8899');
$list   = $einvoice->list(['page' => 1]);

$einvoice->cancel('INV-HTC-2026-8899', ['reason' => 'Sai thông tin']);
file_put_contents('hd.pdf', $einvoice->pdf('INV-HTC-2026-8899'));
file_put_contents('hd.xml', $einvoice->xml('INV-HTC-2026-8899'));
```

### 8. Open Banking (BankHub)

```php
$bankhub = HiTechCloudPay::bankHub();

// 1) Tạo Hosted Link để khách liên kết ngân hàng (consent)
$link = $bankhub->createLinkToken([
    'user_reference' => 'USR_99881',
    'completion_redirect_uri' => 'https://your-site.vn/bank/linked-success',
]);
// Mở $link['hosted_link_url'] trong iframe / redirect

// 2) Sau khi liên kết
$accounts = $bankhub->listBankAccounts();
$balance  = $bankhub->balance('acc_8899aabb');
$txs      = $bankhub->transactions('acc_8899aabb', ['from_date' => '2026-10-01']);
$banks    = $bankhub->banks();
$bankhub->unlink('acc_8899aabb'); // thu hồi consent
```

## Xử lý webhook

HiTechCloud Pay ký webhook bằng HMAC-SHA256. Dùng middleware có sẵn để xác minh chữ ký trước khi xử lý.

```php
use HiTechCloud\Pay\Webhook\VerifyWebhookSignature;

// Biến động số dư (mặc định): header X-HiTechCloud-Signature + X-HiTechCloud-Timestamp
Route::post('/webhooks/hitechcloudpay', [WebhookController::class, 'handle'])
    ->middleware(VerifyWebhookSignature::class);

// Các loại khác:
//   VerifyWebhookSignature::class.':gateway'   (IPN cổng thanh toán)
//   VerifyWebhookSignature::class.':bankhub'   (X-HiTechCloud-BankHub-Signature)
//   VerifyWebhookSignature::class.':einvoice'  (X-HTC-Signature)
```

Trong controller, bóc tách payload:

```php
use HiTechCloud\Pay\Facades\HiTechCloudPay;
use HiTechCloud\Pay\Webhook\WebhookEvents;

public function handle(\Illuminate\Http\Request $request)
{
    $payload = HiTechCloudPay::webhooks()->parse($request->getContent());

    if ($payload->is(WebhookEvents::TRANSACTION_IN)) {
        $code   = $payload->code();        // mã đơn bóc tách từ nội dung CK
        $amount = $payload->amount();      // số tiền vào
        // -> đánh dấu đơn đã thanh toán (xử lý idempotent theo id/referenceCode)
    }

    return response()->json(['success' => true]);
}
```

**Xác minh thủ công** (không dùng middleware):

```php
$ok = HiTechCloudPay::webhooks()->verify(
    $request->getContent(),
    $request->header('X-HiTechCloud-Signature'),
    $request->header('X-HiTechCloud-Timestamp')
);
```

**Đăng ký / giả lập webhook** qua Admin API (cần `HITECHCLOUDPAY_ADMIN_API_KEY`):

```php
HiTechCloudPay::webhooks()->register([
    'url' => 'https://your-site.vn/webhooks/hitechcloudpay',
    'events' => [WebhookEvents::TRANSACTION_IN],
    'authType' => 'hmac',
]);

// Chỉ dùng trên sandbox
HiTechCloudPay::webhooks()->simulate(['event' => WebhookEvents::TRANSACTION_IN]);
```

## Sandbox vs Production

Đặt `HITECHCLOUDPAY_SANDBOX=true` để tự động chuyển sang các host thử nghiệm (ví dụ `pgapi-sandbox.hitechcloud.vn`, `pay-sandbox.hitechcloud.vn`, `sandbox.speaker-api.hitechcloud.vn`, `sandbox.my.hitechcloud.vn`). Dùng cặp Merchant ID / Secret Key hoặc Client ID / Client Secret của môi trường tương ứng.

Có thể override từng host bằng biến môi trường (ví dụ `HITECHCLOUDPAY_GATEWAY_API_URL`) - xem `config/hitechcloudpay.php`.

## Xử lý lỗi / Exception

Mọi lỗi SDK kế thừa `HiTechCloud\Pay\Exceptions\HiTechCloudPayException`:

| Exception | Khi nào |
| --- | --- |
| `ConfigurationException` | Thiếu/sai cấu hình (merchant_id, token, client credentials...) |
| `ApiException` | API trả lỗi (HTTP ≥ 400 hoặc envelope `status: error`) |
| `SignatureException` | Lỗi liên quan tới chữ ký (nếu bạn tự xác minh và chọn ném lỗi) |

```php
use HiTechCloud\Pay\Exceptions\ApiException;

try {
    HiTechCloudPay::transactions()->get('khong-ton-tai');
} catch (ApiException $e) {
    $e->getStatusCode();  // 404
    $e->getErrorCode();   // "NOT_FOUND"
    $e->getMessage();     // thông điệp tiếng Việt từ API
    $e->getBody();        // toàn bộ body phản hồi

    if ($e->isRateLimited()) { /* 429: đợi theo Retry-After */ }
}
```

> SDK tự động thử lại (exponential backoff, ưu tiên header `Retry-After`) khi gặp **429** hoặc **5xx**, và tự thêm `Idempotency-Key` cho các thao tác ghi (tạo VA, hoàn tiền, phát hành hoá đơn...).

## Kiểm thử

```bash
composer install
vendor/bin/phpunit
```

Bộ test dùng `orchestra/testbench` và Guzzle `MockHandler` (không gọi mạng thật).

## SDK hợp nhất - các sản phẩm khác

Dùng facade `HiTechCloud` (hoặc inject `HiTechCloud\HiTechCloud`) để truy cập mọi sản phẩm. Mỗi accessor trả về client của module tương ứng:

```php
use HiTechCloud\Facades\HiTechCloud;

HiTechCloud::pay();      // = module Pay (tương đương facade HiTechCloudPay)
HiTechCloud::ekyc();     // eKYC / VNeID
HiTechCloud::pos();      // POS
HiTechCloud::omni();     // Omnichannel
HiTechCloud::crm();      // CRM
HiTechCloud::waf();      // WAF
HiTechCloud::saleAi();   // Sale AI
HiTechCloud::tools();    // API Tools
HiTechCloud::userApi();  // User API
```

Chỉ cần khai báo `.env` cho những module bạn dùng (xem `config/hitechcloudpay.php` để biết đầy đủ khoá):

```dotenv
# POS
HITECHCLOUDPAY_POS_TOKEN=
HITECHCLOUDPAY_POS_WEBHOOK_SECRET=

# Omnichannel
HITECHCLOUDPAY_OMNI_TOKEN=
HITECHCLOUDPAY_OMNI_WEBHOOK_SECRET=

# CRM (token tĩnh HOẶC OAuth2 client credentials)
HITECHCLOUDPAY_CRM_TOKEN=
HITECHCLOUDPAY_CRM_CLIENT_ID=
HITECHCLOUDPAY_CRM_CLIENT_SECRET=
HITECHCLOUDPAY_CRM_WEBHOOK_SECRET=

# WAF (CSRF -> login -> token)
HITECHCLOUDPAY_WAF_USERNAME=
HITECHCLOUDPAY_WAF_PASSWORD=

# Sale AI
HITECHCLOUDPAY_SALEAI_TOKEN=
HITECHCLOUDPAY_SALEAI_PAGE_TOKEN=
HITECHCLOUDPAY_SALEAI_WEBHOOK_SECRET=

# API Tools
HITECHCLOUDPAY_TOOLS_API_KEY=

# User API (login) / sandbox dùng ote.hitechcloud.vn
HITECHCLOUDPAY_USERAPI_USERNAME=
HITECHCLOUDPAY_USERAPI_PASSWORD=

# eKYC / VNeID (ký RSA) - OFF tới khi khai báo đủ
HITECHCLOUDPAY_EKYC_AGENT_CODE=
HITECHCLOUDPAY_EKYC_PRIVATE_KEY=
HITECHCLOUDPAY_EKYC_AES_KEY=
```

### POS

```php
$pos = HiTechCloud::pos();
$pos->shops()->list();
$pos->products()->list('shop_0a1b2c3d', ['page' => 1]);
$pos->orders()->create('shop_0a1b2c3d', [/* ... */]);
$pos->orders()->updateStatus('shop_0a1b2c3d', 'order_1001', ['status' => 'confirmed']);
$pos->inventory()->adjust('shop_0a1b2c3d', [/* ... */]);
$pos->analytics()->sale('shop_0a1b2c3d', ['from' => '2026-10-01']);
```

### Omnichannel

```php
$omni = HiTechCloud::omni();
$omni->channels()->list();
$conv = $omni->conversations()->list(['status' => 'open']);
$omni->conversations()->sendMessage('cnv_01HZ', ['text' => 'Xin chào']);
$omni->conversations()->assign('cnv_01HZ', ['agent_id' => 'usr_1']);
$omni->stats()->channels(['range' => '7d']);
```

### CRM

```php
$crm = HiTechCloud::crm();
$crm->contacts()->create(['name' => 'Nguyễn Văn A', 'email' => 'a@vd.vn']);
$crm->deals()->update('deal_a19c330e', ['stage_id' => 'won']);
$crm->pipelines()->stages('pl_sales');
$crm->activities()->create(['type' => 'call', 'contact_id' => 'con_1']);
```

### WAF

```php
$waf = HiTechCloud::waf();
$waf->sites()->list();
$waf->certificates()->list();
$waf->reports()->get('123');
$waf->authDefense()->users();
```

### Sale AI

```php
$ai = HiTechCloud::saleAi();
$ai->bots()->list();
$ai->bots()->sendMessage('bot_7c1a9f2e', ['text' => 'Giá bao nhiêu?']);
$ai->ai()->handover(['conversation_id' => 'cnv_1']);
```

### API Tools

```php
$tools = HiTechCloud::tools();
$tools->geoIp()->lookup('8.8.8.8');
$tools->finance()->rates();
$tools->domain()->whois('hitechcloud.vn');
$tools->barcode()->validate('8934588123456');
// Gọi trực tiếp một tool bất kỳ:
$tools->call('/api/health/drugs', ['q' => 'paracetamol']);
```

### User API

```php
$user = HiTechCloud::userApi();
$user->account()->balance();
$user->services()->list();
$user->services()->reboot('svc_123');
$user->domains()->availability('vidu.vn');
```

### eKYC / VNeID

> Module eKYC yêu cầu ký RSA (SHA256withRSA) và đăng ký agent với C06. Lược đồ mã hoá chính xác phụ thuộc hợp đồng tích hợp; module **tắt** tới khi khai báo đủ `ekyc.*`.

```php
$ekyc = HiTechCloud::ekyc();
$tx = $ekyc->session('face-matching')->init([/* payload */]);   // POST /{service-code}/api/agent/init
$ekyc->session('face-matching')->getTransaction(['transaction_id' => $tx['id'] ?? '']);
$ekyc->shareInfo()->init([/* ... */]);
$ekyc->sso()->userInfo([/* ... */]);
```

## Route & Webhook (tất cả module)

SDK tự đăng ký các route nhận webhook (POST) dưới tiền tố cấu hình được (mặc định `hitechcloud/webhooks`), mỗi route đứng sau middleware xác minh chữ ký:

| Route | Loại chữ ký | Header |
| --- | --- | --- |
| `POST {prefix}/pay` | biến động số dư | `X-HiTechCloud-Signature` + `X-HiTechCloud-Timestamp` |
| `POST {prefix}/gateway` | IPN cổng thanh toán | `X-HiTechCloud-Signature` |
| `POST {prefix}/bankhub` | BankHub | `X-HiTechCloud-BankHub-Signature` |
| `POST {prefix}/einvoice` | Hoá đơn điện tử | `X-HTC-Signature` |
| `POST {prefix}/omni` | Omnichannel | `X-HTC-Signature` |
| `POST {prefix}/crm` | CRM | `X-CRM-Signature` |
| `POST {prefix}/sale-ai` | Sale AI | `X-SaleAI-Signature` |
| `POST {prefix}/pos` | POS | `X-HitechPos-Signature` |

Cấu hình trong `config/hitechcloudpay.php` (`webhooks`):

```php
'webhooks' => [
    'enabled'    => true,                    // false để tự quản lý route
    'prefix'     => 'hitechcloud/webhooks',  // HITECHCLOUDPAY_WEBHOOK_PREFIX
    'middleware' => ['api'],
    // ... admin_api_key, admin_url
],
```

Controller mặc định (`HiTechCloud\Http\Controllers\WebhookController`) sẽ: lưu một bản ghi `htc_pay_webhook_events` (dedup theo `event_id`), phát event Laravel và đẩy job hàng đợi. Lắng nghe các event sau để xử lý nghiệp vụ:

```php
use HiTechCloud\Events\WebhookReceived;   // mọi webhook
use HiTechCloud\Events\PaymentSucceeded;
use HiTechCloud\Events\PaymentFailed;
use HiTechCloud\Events\RefundCompleted;
use HiTechCloud\Events\BalanceChanged;

// App\Providers\EventServiceProvider
protected $listen = [
    PaymentSucceeded::class => [MarkOrderPaid::class],
    BalanceChanged::class   => [ReconcileTransaction::class],
];
```

Job `HiTechCloud\Jobs\ProcessWebhookJob` (implements `ShouldQueue`) đánh dấu `processed_at` sau khi xử lý; bạn có thể mở rộng hoặc thay thế listener theo nhu cầu.

Muốn **tự định tuyến**: đặt `HITECHCLOUDPAY_WEBHOOK_ROUTES=false` rồi tự khai báo route với middleware `HiTechCloud\Pay\Webhook\VerifyWebhookSignature` (tham số loại: `transaction|gateway|bankhub|einvoice|omni|crm|saleai|pos`).

## Giấy phép

Mã nguồn mở theo giấy phép [MIT](LICENSE). © HiTechCloud.
