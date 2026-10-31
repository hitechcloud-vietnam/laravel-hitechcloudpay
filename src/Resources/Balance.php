<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Resources;

/**
 * Số dư (Balance).
 *
 * Số dư khả dụng tức thời được cung cấp qua BankHub Open Banking
 * (GET /v1/bank-accounts/{id}/balance). Lớp này là lối tắt tiện dụng, uỷ quyền
 * cho {@see BankHub} để tái sử dụng access token đã cache.
 *
 * Lưu ý: nên cache kết quả tối đa ~60 giây và ưu tiên nhận biến động số dư qua
 * webhook thay vì gọi liên tục (theo khuyến nghị trong tài liệu).
 */
class Balance extends AbstractResource
{
    protected ?BankHub $bankHub = null;

    /**
     * Số dư tức thời của một tài khoản ngân hàng đã liên kết qua BankHub.
     *
     * @return array<string,mixed>
     */
    public function get(string $accountId): array
    {
        return $this->bankHub()->balance($accountId);
    }

    protected function bankHub(): BankHub
    {
        if ($this->bankHub === null) {
            $this->bankHub = new BankHub($this->http, $this->config);
        }

        return $this->bankHub;
    }
}
