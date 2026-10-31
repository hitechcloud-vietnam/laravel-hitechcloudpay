<?php

declare(strict_types=1);

namespace HiTechCloud\Exceptions;

use Exception;

/**
 * Lớp ngoại lệ gốc dùng chung cho toàn bộ HiTechCloud Laravel SDK.
 *
 * Mọi module (Pay, eKYC, POS, Omni, CRM, WAF, Sale AI, Tools, UserAPI) đều ném
 * các ngoại lệ kế thừa từ lớp này, nhờ đó ứng dụng có thể bắt một lớp duy nhất
 * để xử lý mọi lỗi phát sinh từ SDK.
 *
 * (Module Pay vẫn giữ riêng {@see \HiTechCloud\Pay\Exceptions\HiTechCloudPayException}
 * để tương thích ngược; lớp này dùng cho các module bổ sung.)
 */
class HiTechCloudException extends Exception
{
}
