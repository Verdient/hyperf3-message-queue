<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Override;
use Psr\Container\ContainerInterface;
use Verdient\Hyperf3\Task\Event\EventDispatcher;
use Verdient\Hyperf3\Task\TaskManager;

use function Hyperf\Support\make;

/**
 * 注册消息队列监听器
 *
 * @author Verdient。
 */
class RegisterMessageQueueListener implements ListenerInterface
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
            BeforeMainServerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * @param BeforeMainServerStart|MainCoroutineServerStart $event 事件
     *
     * @author Verdient。
     */
    #[Override]
    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class)->get('message_queue');

        if ($config['enable'] !== true) {
            return;
        }

        $eventDispatcher = new EventDispatcher;

        foreach (MessageQueueManager::all() as $class => $identifier) {

            $dispatcher = make($class);

            $dispatcher->setEventDispatcher($eventDispatcher);

            $configuration = TaskManager::parse($class);

            if ($configuration->identifier === null) {

                if ($identifier) {
                    $configuration->identifier = $identifier;
                } else {
                    $identifier = $class;

                    foreach (
                        [
                            'App\Task\MessageQueues\\',
                            'App\Task\MessageQueue\\',
                            'App\MessageQueues\\',
                            'App\MessageQueue\\'
                        ] as $prefix
                    ) {
                        if ($identifier !== $prefix && str_starts_with($identifier, $prefix)) {
                            $identifier = substr($identifier, strlen($prefix));
                            break;
                        }
                    }

                    foreach (['MessageQueue', 'Queue'] as $suffix) {
                        if ($identifier !== $suffix && str_ends_with($identifier, $suffix)) {
                            $identifier = substr($identifier, 0, -strlen($suffix));
                            break;
                        }
                    }

                    $configuration->identifier = 'MessageQueue.' . str_replace('\\', '.', $identifier);
                }
            }

            TaskManager::add(
                $dispatcher,
                $configuration,
            );
        }
    }
}
