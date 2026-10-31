<?php

declare(strict_types=1);

namespace HiTechCloud\Pay\Exceptions;

/**
 * Ném ra khi thiếu hoặc sai cấu hình (ví dụ: chưa khai báo merchant_id,
 * secret_key, access token hay client credentials cần thiết).
 */
class ConfigurationException extends HiTechCloudPayException
{
}
