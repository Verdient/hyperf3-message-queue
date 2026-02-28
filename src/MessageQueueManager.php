<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Verdient\Hyperf3\MessageQueue\Annotation\MessageQueueCollector;
use Verdient\MessageQueue\DispatcherInterface;

/**
 * 消息队列管理器
 *
 * @author Verdient。
 */
class MessageQueueManager
{
    /**
     * 消息队列集合
     *
     * @author Verdient。
     */
    protected static ?array $messageQueues = null;

    /**
     * 初始化消息队列
     *
     * @author Verdient。
     */
    protected static function initMessageQueues(): void
    {
        if (static::$messageQueues === null) {
            static::$messageQueues = [];

            foreach (array_keys(MessageQueueCollector::list()) as $class) {
                static::$messageQueues[$class] = null;
            }
        }
    }

    /**
     * 添加消息队列
     *
     * @param class-string<DispatcherInterface> $class 类名
     * @param ?string $identifier 标识符
     *
     * @author Verdient。
     */
    public static function add(
        string $class,
        ?string $identifier = null
    ): void {
        static::initMessageQueues();
        static::$messageQueues[$class] = $identifier;
    }

    /**
     * 获取消息队列集合
     *
     * @return array<class-string<DispatcherInterface>,?string>
     * @author Verdient。
     */
    public static function all(): array
    {
        static::initMessageQueues();

        return static::$messageQueues;
    }
}
