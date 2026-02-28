<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Override;
use Psr\Container\ContainerInterface;
use Verdient\Hyperf3\Task\LoggerManager;

/**
 * 注册记录器规则监听器
 *
 * @author Verdient。
 */
class RegisterLoggerRuleListener implements ListenerInterface
{
    /**
     * @param ContainerInterface $container 容器
     *
     * @author Verdient。
     */
    public function __construct(protected ContainerInterface $container) {}

    /**
     * @author Verdient。
     */
    #[Override]
    public function listen(): array
    {
        return [
            BootApplication::class
        ];
    }

    /**
     * @param BootApplication $event 事件
     *
     * @author Verdient。
     */
    #[Override]
    public function process(object $event): void
    {
        $command = $_SERVER['argv'][1] ?? null;

        if ($command !== 'start') {
            return;
        }

        LoggerManager::registerRule('App\Task\\', ['MessageQueue', 'RedisQueue', 'Queue']);
        LoggerManager::registerRule('App\Tasks\\', ['MessageQueue', 'RedisQueue', 'Queue']);
    }
}
