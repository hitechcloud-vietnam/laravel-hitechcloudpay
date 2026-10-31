<?php

declare(strict_types=1);

namespace HiTechCloud\Pos;

use HiTechCloud\Pos\Resources\Analytics;
use HiTechCloud\Pos\Resources\Customers;
use HiTechCloud\Pos\Resources\Geo;
use HiTechCloud\Pos\Resources\Inventory;
use HiTechCloud\Pos\Resources\Orders;
use HiTechCloud\Pos\Resources\Products;
use HiTechCloud\Pos\Resources\Shops;
use HiTechCloud\Support\Client;

/**
 * Client HiTechPos (pos.hitechcloud.vn).
 *
 * - Base URL : https://pos.hitechcloud.vn/api/v1
 * - Xác thực : Bearer access token (cấp trong dashboard POS).
 * - Hầu hết endpoint nghiệp vụ nằm dưới `/shops/{shop_id}/...`.
 * - Webhook  : header `X-HitechPos-Signature` (xác minh qua middleware loại `pos`).
 */
class PosClient extends Client
{
    protected function baseUrl(): string
    {
        return rtrim($this->requireCfg('pos.base_url'), '/') . '/api/v1';
    }

    /**
     * @return array<string,string>
     */
    protected function authHeaders(): array
    {
        return $this->bearerHeader($this->requireCfg('pos.token'));
    }

    public function shops(): Shops
    {
        return $this->resource('shops', Shops::class);
    }

    public function products(): Products
    {
        return $this->resource('products', Products::class);
    }

    public function orders(): Orders
    {
        return $this->resource('orders', Orders::class);
    }

    public function customers(): Customers
    {
        return $this->resource('customers', Customers::class);
    }

    public function inventory(): Inventory
    {
        return $this->resource('inventory', Inventory::class);
    }

    public function analytics(): Analytics
    {
        return $this->resource('analytics', Analytics::class);
    }

    public function geo(): Geo
    {
        return $this->resource('geo', Geo::class);
    }
}
