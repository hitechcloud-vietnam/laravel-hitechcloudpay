<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Webhook;

use Closure;
use HiTechCloud\Pay\Support\Signature;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Laravel xác minh chữ ký webhook trước khi cho request đi tiếp.
 *
 * Cách dùng (routes/web.php hoặc routes/api.php):
 *
 *   use HiTechCloud\Pay\Webhook\VerifyWebhookSignature;
 *
 *   Route::post('/webhooks/hitechcloudpay', WebhookController::class)
 *        ->middleware(VerifyWebhookSignature::class);          // mặc định: biến động số dư
 *
 *   Route::post('/webhooks/bankhub', BankHubController::class)
 *        ->middleware(VerifyWebhookSignature::class.':bankhub');
 *
 * Các "type" hỗ trợ: transaction (mặc định), gateway, bankhub, einvoice,
 * omni, crm, saleai, pos.
 */
class VerifyWebhookSignature
{
    /**
     * @param string $type transaction|gateway|bankhub|einvoice|omni|crm|saleai|pos
     */
    public function handle(Request $request, Closure $next, string $type = 'transaction')
    {
        $rawBody = $request->getContent();

        $verified = $this->verifyByType($type, $request, $rawBody);

        if (! $verified) {
            return response()->json([
                'success' => false,
                'message' => 'Chữ ký webhook không hợp lệ.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    protected function verifyByType(string $type, Request $request, string $rawBody): bool
    {
        switch ($type) {
            case 'bankhub':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-HiTechCloud-BankHub-Signature', ''),
                    (string) config('hitechcloudpay.bankhub.webhook_secret')
                );

            case 'einvoice':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-HTC-Signature', ''),
                    (string) config('hitechcloudpay.einvoice.webhook_secret')
                );

            case 'gateway':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-HiTechCloud-Signature', ''),
                    (string) config('hitechcloudpay.gateway.secret_key')
                );

            case 'omni':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-HTC-Signature', ''),
                    (string) config('hitechcloudpay.omni.webhook_secret')
                );

            case 'crm':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-CRM-Signature', ''),
                    (string) config('hitechcloudpay.crm.webhook_secret')
                );

            case 'saleai':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-SaleAI-Signature', ''),
                    (string) config('hitechcloudpay.saleai.webhook_secret')
                );

            case 'pos':
                return Signature::verifyRawBody(
                    $rawBody,
                    (string) $request->header('X-HitechPos-Signature', ''),
                    (string) config('hitechcloudpay.pos.webhook_secret')
                );

            case 'transaction':
            default:
                return Signature::verifyWebhook(
                    $rawBody,
                    (string) $request->header('X-HiTechCloud-Signature', ''),
                    (string) config('hitechcloudpay.webhook_secret'),
                    (string) $request->header('X-HiTechCloud-Timestamp', ''),
                    (int) config('hitechcloudpay.webhook_tolerance', 300)
                );
        }
    }
}
