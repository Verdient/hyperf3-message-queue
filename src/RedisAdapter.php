<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Hyperf\Redis\RedisProxy;
use Override;
use Redis;
use RuntimeException;
use Throwable;
use Verdient\MessageQueue\AdapterInterface;
use Verdient\MessageQueue\MessageInterface;

use function Hyperf\Config\config;

/**
 * Redis队列适配器
 *
 * @author Verdient。
 */
class RedisAdapter implements AdapterInterface
{
    /**
     * @param ?string $pool 连接池名称
     *
     * @author Verdient。
     */
    public function __construct(protected ?string $pool = null) {
        if ($pool === null) {
            $pools = array_keys(config('redis', []));

            if (empty($pools)) {
                throw new RuntimeException('No redis connection pool was found.');
            }

            if (in_array('default', $pools)) {
                $this->pool = 'default';
            } else {
                $this->pool = $pools[0];
            }
        }
    }

    /**
     * 获取连接对象
     *
     * @author Verdient。
     */
    protected function connection(): Redis|RedisProxy
    {
        return RedisConnectionManager::get($this->pool);
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function idleGap(): int|float
    {
        return 0.1;
    }

    /**
     * 获取队列键名
     *
     * @param string $queue 队列名称
     *
     * @author Verdient。
     */
    protected function getKey(string $queue): string
    {
        return 'message.queue.list.' . $this->pool . '.' . $queue;
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function push(MessageInterface $message): bool
    {
        return $this->connection()->lpush($this->getKey($message->queue()), serialize($message)) > 0;
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function pop(string $queue): ?MessageInterface
    {
        if ($message = $this->connection()->rpop($this->getKey($queue))) {
            return unserialize($message);
        }

        return null;
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function commit(MessageInterface $message): void {}

    /**
     * @author Verdient。
     */
    #[Override]
    public function retry(MessageInterface $message): void
    {
        if ($this->push($message) === false) {
            throw new RuntimeException('Message ' . $message::class . ' failed to retry and could not be pushed into the queue.');
        }
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function fault(MessageInterface $message, Throwable $throwable): void {}
}
