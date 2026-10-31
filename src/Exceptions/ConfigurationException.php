<?php

declare(strict_types=1);

namespace HiTechCloud\Exceptions;

/**
 * Ném ra khi thiếu hoặc sai cấu hình của một module (host, token, client
 * credentials, API key...).
 */
class ConfigurationException extends HiTechCloudException
{
}
