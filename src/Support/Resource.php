<?php

declare(strict_types=1);

namespace HiTechCloud\Support;

/**
 * Lớp Resource cơ sở dùng chung cho các module bổ sung.
 *
 * Giữ tham chiếu tới {@see Client} của module và cung cấp tiện ích bóc tách
 * envelope `{ success, data }` hay `{ status, data }`.
 */
abstract class Resource
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Lấy trường `data` trong envelope nếu có, nếu không trả nguyên body.
     *
     * @param array<string,mixed>|string $response
     * @return mixed
     */
    protected function data($response)
    {
        if (is_array($response) && array_key_exists('data', $response)) {
            return $response['data'];
        }

        return $response;
    }
}
