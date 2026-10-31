<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\DataObjects;

/**
 * Giá trị đại diện cho một phiên thanh toán (checkout) của cổng thanh toán.
 *
 * Vì checkout là form POST từ trình duyệt khách hàng, đối tượng này gói lại
 * URL checkout và tập tham số ĐÃ được ký (bao gồm `signature`), kèm tiện ích
 * sinh form HTML tự động submit.
 */
class PaymentSession
{
    public string $checkoutUrl;

    /** @var array<string,mixed> */
    public array $params;

    /**
     * @param array<string,mixed> $params
     */
    public function __construct(string $checkoutUrl, array $params)
    {
        $this->checkoutUrl = $checkoutUrl;
        $this->params = $params;
    }

    public function orderInvoiceNumber(): ?string
    {
        return isset($this->params['order_invoice_number'])
            ? (string) $this->params['order_invoice_number']
            : null;
    }

    public function signature(): ?string
    {
        return isset($this->params['signature']) ? (string) $this->params['signature'] : null;
    }

    /**
     * Sinh form HTML tự động submit sang trang checkout của HiTechCloud Pay.
     */
    public function toAutoSubmitForm(string $formId = 'hitechcloudpay_form'): string
    {
        $inputs = '';
        foreach ($this->params as $key => $value) {
            $inputs .= sprintf(
                '<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars((string) $key, ENT_QUOTES),
                htmlspecialchars((string) $value, ENT_QUOTES)
            );
        }

        return sprintf(
            '<form id="%s" action="%s" method="POST">%s</form>'
            . '<script>document.getElementById(%s).submit();</script>',
            htmlspecialchars($formId, ENT_QUOTES),
            htmlspecialchars($this->checkoutUrl, ENT_QUOTES),
            $inputs,
            json_encode($formId)
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'checkout_url' => $this->checkoutUrl,
            'params' => $this->params,
        ];
    }
}
