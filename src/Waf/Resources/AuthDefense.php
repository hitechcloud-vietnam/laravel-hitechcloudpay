<?php

declare(strict_types=1);

namespace HiTechCloud\Waf\Resources;

use HiTechCloud\Support\Resource;

/**
 * Phòng vệ xác thực (auth_defense): quản lý người dùng, nguồn đăng nhập, khoá/mở.
 *
 * - GET    /open/auth_defense/user           : danh sách người dùng.
 * - GET    /open/auth_defense/user/{id}      : chi tiết.
 * - POST   /open/auth_defense/user/{id}/unbind     : gỡ liên kết.
 * - POST   /open/auth_defense/user/{id}/reset_totp : đặt lại 2FA.
 * - GET    /open/auth_defense/source         : danh sách nguồn.
 */
class AuthDefense extends Resource
{
    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function users(array $filters = []): array
    {
        return (array) $this->client->get('/open/auth_defense/user', $filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function user(string $id): array
    {
        return (array) $this->client->get('/open/auth_defense/user/' . rawurlencode($id));
    }

    /**
     * @return array<string,mixed>
     */
    public function unbind(string $id): array
    {
        return (array) $this->client->post('/open/auth_defense/user/' . rawurlencode($id) . '/unbind');
    }

    /**
     * @return array<string,mixed>
     */
    public function resetTotp(string $id): array
    {
        return (array) $this->client->post('/open/auth_defense/user/' . rawurlencode($id) . '/reset_totp');
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function sources(array $filters = []): array
    {
        return (array) $this->client->get('/open/auth_defense/source', $filters);
    }
}
