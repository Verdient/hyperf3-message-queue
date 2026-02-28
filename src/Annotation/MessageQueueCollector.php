<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue\Annotation;

use Hyperf\Di\MetadataCollector;

/**
 * 消息队列收集器
 *
 * @method static ?MessageQueue get(string $key, $default = null)
 * @method static array<class-string,MessageQueue> list()
 *
 * @author Verdient。
 */
class MessageQueueCollector extends MetadataCollector
{
    /**
     * @inheritdoc
     *
     * @author Verdient。
     */
    protected static array $container = [];

    /**
     * 收集类
     *
     * @param class-string $className 类名
     * @param MessageQueue $annotation 注解
     *
     * @author Verdient。
     */
    public static function collectClass(string $className, MessageQueue $annotation): void
    {
        static::$container[$className] = $annotation;
    }
}
