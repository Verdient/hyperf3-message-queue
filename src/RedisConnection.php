<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Redis;
use RedisException;

use function Hyperf\Config\config;

/**
 * Redis连接
 *
 * @author Verdient。
 */
class RedisConnection
{
    /**
     * 连接对象
     *
     * @author Verdient。
     */
    protected ?Redis $redis = null;

    /**
     * @param ?string $pool 连接池名称
     *
     * @author Verdient。
     */
    public function __construct(protected string $pool) {}

    /**
     * 获取Redis对象
     *
     * @author Verdient。
     */
    protected function redis(): Redis
    {
        if ($this->redis === null) {
            $this->redis = new Redis();

            $config = config('redis.' . $this->pool);

            try {

                if (!$this->redis->connect($config['host'], $config['port'])) {
                    throw new RedisException('Connection refused.');
                }

                if (isset($config['auth']) && $this->redis->auth($config['auth']) === false) {
                    throw new RedisException('Authentication failed.');
                }

                if (isset($config['db']) && $this->redis->select($config['db']) === false) {
                    throw new RedisException('Failed to select database.');
                }
            } catch (\Throwable $e) {
                if ($this->redis->isConnected()) {
                    $this->redis->close();
                }

                $this->redis = null;

                sleep(1);

                throw new RedisException('Failed to connect to Redis server ' . $config['host'] . ':' . $config['port'] . '.', 0, $e);
            }
        }

        return $this->redis;
    }

    /**
     * 调用Redis方法
     *
     * @param string $method 方法名
     * @param mixed ...$arguments 参数
     *
     * @author Verdient。
     */
    protected function call(string $method, ...$arguments): mixed
    {
        try {
            return $this->redis()->$method(...$arguments);
        } catch (\Throwable $e) {
            if (!$this->redis()->isConnected()) {
                $this->redis = null;
            }
            throw $e;
        }
    }

    /**
     * 推送元素
     *
     * @param string $key 名称
     * @param string $value 值
     *
     * @author Verdient。
     */
    public function push(string $key, string $value): bool
    {
        return $this->call('lPush', $key, $value) !== false;
    }

    /**
     * 弹出元素
     *
     * @param string $key 名称
     *
     * @author Verdient。
     */
    public function pop(string $key): ?string
    {
        return $this->call('rPop', $key) ?: null;
    }

    /**
     * 析构
     *
     * @author Verdient。
     */
    public function __destruct()
    {
        if ($this->redis && $this->redis->isConnected()) {
            $this->redis->close();
            $this->redis = null;
        }
    }
}
