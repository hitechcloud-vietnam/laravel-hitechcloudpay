# Changelog

Tất cả các thay đổi đáng chú ý của gói `hitechcloud/laravel-hitechcloudpay` được ghi lại trong file này.

Định dạng dựa trên [Keep a Changelog](https://keepachangelog.com/vi/1.0.0/),
và dự án tuân theo [Semantic Versioning](https://semver.org/lang/vi/).

## [1.0.0] - 2026-11-01

### Added
- Phát hành phiên bản đầu tiên của SDK Laravel cho HiTechCloud Pay.
- **Cổng thanh toán (Gateway)**: tạo phiên thanh toán (checkout/init) có ký HMAC-SHA256, tra cứu trạng thái đơn, hoàn tiền, liệt kê/đối soát đơn hàng.
- **Pay API v2 (Core)**: tài khoản ngân hàng (`bank-accounts`), tài khoản ảo/Virtual Account (`sub-accounts`), giao dịch (`transactions`).
- **BankHub (Open Banking)**: lấy access token, tạo Hosted Link, liệt kê tài khoản, tra cứu số dư tức thời, sao kê, huỷ liên kết (consent), danh sách ngân hàng.
- **Số dư (Balance)**: tra cứu số dư khả dụng tức thời qua BankHub.
- **Loa SoundBox (Speaker)**: cấp token, ghép nối (check/request/verify/unpair), bắn thông báo giao dịch, kiểm tra trạng thái phát, cấu hình, liệt kê loa.
- **Hoá đơn điện tử (eInvoice)**: cấp token, phát hành hoá đơn, theo dõi trạng thái, phát hành hàng loạt, huỷ/điều chỉnh/thay thế, chi tiết, danh sách, tải PDF/XML.
- **Webhook**: parser payload, middleware xác minh chữ ký (HMAC-SHA256 timing-safe), hằng số tên sự kiện, đăng ký/giả lập webhook qua Admin API.
- Hỗ trợ Sandbox và Production, idempotency-key cho thao tác ghi, retry tự động khi gặp 429/5xx.
- Facade `HiTechCloudPay`, auto-discovery cho Laravel 9–13, publish config.

[1.0.0]: https://github.com/hitechcloud-vietnam/laravel-hitechcloudpay/releases/tag/v1.0.0
