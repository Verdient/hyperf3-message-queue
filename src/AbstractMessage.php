<?php

namespace Verdient\Hyperf3\MessageQueue;

use Override;
use Verdient\Hyperf3\Logger\HasLogger;
use Verdient\MessageQueue\AbstractMessage as MessageQueueAbstractMessage;
use Verdient\MessageQueue\AdapterInterface;
use Verdient\MessageQueue\MessageInterface;

/**
 * 消息抽象类
 *
 * @author Verdient。
 */
abstract class AbstractMessage extends MessageQueueAbstractMessage
{
    use HasLogger;

    /**
     * 获取适配器
     *
     * @author Verdient。
     */
    abstract protected function adapter(): AdapterInterface;

    /**
     * @author Verdient。
     */
    #[Override]
    public function push(): bool
    {
        return $this->adapter()->push($this);
    }

    /**
     * 创建默认的记录器的组名集合
     *
     * @return array<int|string,string>
     * @author Verdient。
     */
    protected function groupsForCreateDefaultLogger(): array
    {
        return [static::class => MessageInterface::class];
    }
}
