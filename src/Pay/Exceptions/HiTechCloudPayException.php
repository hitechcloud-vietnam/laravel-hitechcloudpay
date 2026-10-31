<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Exceptions;

use Exception;

/**
 * Lớp ngoại lệ gốc của toàn bộ SDK HiTechCloud Pay.
 *
 * Bắt lớp này để xử lý mọi lỗi phát sinh từ SDK (cấu hình, chữ ký, API...).
 */
class HiTechCloudPayException extends Exception
{
}
