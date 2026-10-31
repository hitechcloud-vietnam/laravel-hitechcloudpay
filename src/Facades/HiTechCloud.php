<?php

declare(strict_types=1);

namespace HiTechCloud\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade hợp nhất cho toàn bộ HiTechCloud SDK.
 *
 * @method static \HiTechCloud\Pay\HiTechCloudPay pay()
 * @method static \HiTechCloud\Ekyc\EkycClient ekyc()
 * @method static \HiTechCloud\Pos\PosClient pos()
 * @method static \HiTechCloud\Omni\OmniClient omni()
 * @method static \HiTechCloud\Crm\CrmClient crm()
 * @method static \HiTechCloud\Waf\WafClient waf()
 * @method static \HiTechCloud\SaleAi\SaleAiClient saleAi()
 * @method static \HiTechCloud\Tools\ToolsClient tools()
 * @method static \HiTechCloud\UserApi\UserApiClient userApi()
 * @method static array config()
 *
 * @see \HiTechCloud\HiTechCloud
 */
class HiTechCloud extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hitechcloud';
    }
}
