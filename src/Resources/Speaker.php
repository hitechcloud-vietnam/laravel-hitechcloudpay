<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * Loa thông báo SoundBox (Speaker).
 *
 * Base URL: speaker-api.hitechcloud.vn/devices/v1 (sandbox: sandbox.speaker-api...).
 * Luồng: lấy token (POST /token/create, Basic Auth) rồi dùng Bearer cho thiết bị.
 *
 * - POST /token/create                          : lấy access token.
 * - POST /speaker/pair/check                    : kiểm tra trạng thái online & liên kết.
 * - POST /speaker/pair/request                  : yêu cầu ghép nối (loa đọc OTP 4 số).
 * - POST /speaker/pair/verify                   : xác thực OTP hoàn tất ghép nối.
 * - POST /speaker/pair/unpair                   : huỷ ghép nối.
 * - POST /speaker/transactions/notify           : bắn thông báo giao dịch (loa đọc tiền).
 * - POST /speaker/transactions/check_notify     : kiểm tra trạng thái phát âm thanh.
 * - POST /speaker/config                        : tuỳ biến nội dung/giọng đọc.
 * - POST /speaker/list                          : liệt kê loa.
 */
class Speaker extends TokenAwareResource
{
    protected function tokenUrl(): string
    {
        return $this->url($this->requireCfg('speaker.base_url'), '/devices/v1/token/create');
    }

    protected function tokenCredentials(): array
    {
        return [
            $this->requireCfg('speaker.client_id'),
            $this->requireCfg('speaker.client_secret'),
        ];
    }

    protected function parseTokenResponse(array $response): array
    {
        $token = (string) ($response['access_token'] ?? '');
        $ttl = (int) ($response['expires_in'] ?? 0);

        return [$token, $ttl];
    }

    /**
     * Lấy access token (chuỗi) cho các thao tác thiết bị.
     */
    public function token(): string
    {
        return $this->accessToken(true);
    }

    /**
     * Kiểm tra trạng thái online & liên kết của loa theo Hardware ID.
     *
     * @return array<string,mixed>
     */
    public function pairCheck(string $hardwareId): array
    {
        return $this->post('/devices/v1/speaker/pair/check', ['hardwareId' => $hardwareId]);
    }

    /**
     * Gửi yêu cầu ghép nối (loa đọc to mã OTP 4 số), trả về session_id.
     *
     * @return array<string,mixed>
     */
    public function pairRequest(string $hardwareId): array
    {
        return $this->post('/devices/v1/speaker/pair/request', ['hardwareId' => $hardwareId]);
    }

    /**
     * Xác thực mã OTP để hoàn tất ghép nối.
     *
     * @return array<string,mixed>
     */
    public function pairVerify(string $hardwareId, string $sessionId, string $otp): array
    {
        return $this->post('/devices/v1/speaker/pair/verify', [
            'hardwareId' => $hardwareId,
            'session_id' => $sessionId,
            'otp' => $otp,
        ]);
    }

    /**
     * Huỷ ghép nối một loa.
     *
     * @return array<string,mixed>
     */
    public function unpair(string $hardwareId): array
    {
        return $this->post('/devices/v1/speaker/pair/unpair', ['hardwareId' => $hardwareId]);
    }

    /**
     * Bắn lệnh thông báo giao dịch để loa đọc số tiền.
     *
     * Tham số tuỳ chọn ($options): voiceType, prefix, suffix, speed.
     *
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    public function notify(string $hardwareId, int $amount, string $requestId, array $options = []): array
    {
        $payload = array_merge([
            'hardwareId' => $hardwareId,
            'amount' => $amount,
            'requestId' => $requestId,
        ], $options);

        return $this->post('/devices/v1/speaker/transactions/notify', $payload, true);
    }

    /**
     * Kiểm tra trạng thái phát âm thanh của một yêu cầu notify.
     *
     * @return array<string,mixed>
     */
    public function checkNotify(string $requestId): array
    {
        return $this->post('/devices/v1/speaker/transactions/check_notify', ['requestId' => $requestId]);
    }

    /**
     * Cấu hình / tuỳ biến nội dung phát của loa.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function config(array $payload): array
    {
        return $this->post('/devices/v1/speaker/config', $payload);
    }

    /**
     * Liệt kê các loa thuộc tài khoản.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function listDevices(array $payload = []): array
    {
        return $this->post('/devices/v1/speaker/list', $payload);
    }

    /**
     * Trợ giúp gửi POST JSON với Bearer token tới base URL của Speaker.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    protected function post(string $path, array $payload, bool $idempotent = false): array
    {
        $response = $this->http->request(
            'POST',
            $this->url($this->requireCfg('speaker.base_url'), $path),
            [
                'headers' => $this->authHeaders() + ['Content-Type' => 'application/json'],
                'json' => $payload,
                'idempotent' => $idempotent,
            ]
        );

        return is_array($response) ? $response : [];
    }
}
