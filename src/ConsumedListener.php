<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Hyperf\Event\Contract\ListenerInterface;
use Override;
use Psr\Log\LoggerInterface;
use Verdient\Hyperf3\Task\Logger\ConsumeLoggerInterface;
use Verdient\Hyperf3\Task\LoggerManager;
use Verdient\MessageQueue\Event\Consumed;

/**
 * 消费事件监听器
 *
 * @author Verdient。
 */
class ConsumedListener implements ListenerInterface
{
    /**
     * 缓存的日志记录器
     *
     * @author Verdient。
     */
    protected array $loggers = [];

    /**
     * @author Verdient。
     */
    #[Override]
    public function listen(): array
    {
        return [
            Consumed::class
        ];
    }

    /**
     * 获取记录器
     *
     * @param Consumed $event 事件
     *
     * @author Verdient。
     */
    protected function getLogger(Consumed $event): LoggerInterface
    {
        $class = $event->dispatcher::class;

        if (!isset($this->loggers[$class])) {
            $this->loggers[$class] = LoggerManager::create($class, ConsumeLoggerInterface::class);
        }

        return $this->loggers[$class];
    }

    /**
     * @param Consumed $event 事件
     *
     * @author Verdient。
     */
    #[Override]
    public function process(object $event): void
    {
        $cost = $event->endAt - $event->startAt;

        $this->getLogger($event)
            ->info(sprintf('消息 %s 消费成功，耗时 %.4f 秒。', $event->message::class, $cost));

        if ($event->message instanceof AbstractMessage) {
            $event->message->logger()->info(sprintf('消费成功，耗时 %.4f 秒。', $cost));
        }
    }
}
