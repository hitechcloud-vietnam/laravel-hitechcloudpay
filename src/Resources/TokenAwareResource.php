<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * Lớp cơ sở cho các sản phẩm đổi client_id/client_secret lấy Bearer access token
 * (BankHub, Speaker, eInvoice).
 *
 * Tự động lấy và cache access token trong vòng đời instance, chủ động làm mới
 * trước khi hết hạn một khoảng an toàn.
 */
abstract class TokenAwareResource extends AbstractResource
{
    protected ?string $accessToken = null;

    /** @var int Thời điểm (unix) token hết hạn; 0 nghĩa là chưa biết. */
    protected int $tokenExpiresAt = 0;

    /** Số giây làm mới token trước khi thực sự hết hạn. */
    protected int $tokenSkew = 30;

    /**
     * URL đầy đủ của endpoint cấp token.
     */
    abstract protected function tokenUrl(): string;

    /**
     * Cặp [client_id, client_secret] dùng Basic Auth khi lấy token.
     *
     * @return array{0:string,1:string}
     */
    abstract protected function tokenCredentials(): array;

    /**
     * Trích access token và thời hạn (giây) từ phản hồi cấp token.
     *
     * @param array<string,mixed> $response
     * @return array{0:string,1:int} [access_token, ttl_seconds]
     */
    abstract protected function parseTokenResponse(array $response): array;

    /**
     * Content-Type dùng khi gọi endpoint token (một số host yêu cầu form-urlencoded).
     */
    protected function tokenContentType(): string
    {
        return 'application/json';
    }

    /**
     * Lấy access token hợp lệ (dùng cache, tự làm mới khi hết hạn).
     */
    public function accessToken(bool $forceRefresh = false): string
    {
        $now = time();

        if (! $forceRefresh && $this->accessToken !== null) {
            if ($this->tokenExpiresAt === 0 || $now < ($this->tokenExpiresAt - $this->tokenSkew)) {
                return $this->accessToken;
            }
        }

        [$clientId, $clientSecret] = $this->tokenCredentials();

        $options = [
            'headers' => $this->basicHeader($clientId, $clientSecret)
                + ['Content-Type' => $this->tokenContentType()],
        ];

        // Gửi body rỗng đúng kiểu Content-Type đã khai báo.
        if ($this->tokenContentType() === 'application/x-www-form-urlencoded') {
            $options['form'] = [];
        } else {
            $options['json'] = new \stdClass();
        }

        $response = $this->http->request('POST', $this->tokenUrl(), $options);
        $response = is_array($response) ? $response : [];

        [$token, $ttl] = $this->parseTokenResponse($response);

        $this->accessToken = $token;
        $this->tokenExpiresAt = $ttl > 0 ? ($now + $ttl) : 0;

        return $token;
    }

    /**
     * Gán sẵn một access token (ví dụ token đã cache ở nơi khác).
     */
    public function setAccessToken(string $token, int $ttl = 0): self
    {
        $this->accessToken = $token;
        $this->tokenExpiresAt = $ttl > 0 ? (time() + $ttl) : 0;

        return $this;
    }

    /**
     * Header Bearer dùng access token hiện hành.
     *
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        return $this->bearerHeader($this->accessToken());
    }
}
