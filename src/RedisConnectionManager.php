<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Hyperf\Redis\RedisProxy;
use function Hyperf\Support\make;

/**
 * Redis连接管理器
 *
 * @author Verdient。
 */
class RedisConnectionManager
{
    /**
     * @var array<int,array<string,RedisProxy>> 缓存的连接
     *
     * @author Verdient。
     */
    protected static array $connections = [];

    /**
     * 创建Redis连接
     *
     * @param string $pool 连接池名称
     *
     * @author Verdient。
     */
    protected static function create(string $pool): RedisProxy
    {
        return make(RedisProxy::class, ['pool' => $pool]);
    }

    /**
     * 获取连接对象
     *
     * @author Verdient。
     */
    public static function get(string $pool): RedisProxy
    {
        $pid = getmypid();

        if (!isset(static::$connections[$pid])) {
            static::$connections[$pid] = [];
        }

        foreach (array_diff(array_keys(static::$connections), [$pid]) as $needlessPid) {
            unset(static::$connections[$needlessPid]);
        }

        if (!isset(static::$connections[$pid][$pool])) {
            static::$connections[$pid][$pool] = static::create($pool);
        }

        return static::$connections[$pid][$pool];
    }
}
