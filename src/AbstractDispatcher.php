<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Verdient\MessageQueue\AbstractDispatcher as MessageQueueAbstractDispatcher;
use Verdient\MessageQueue\AdapterInterface;

/**
 * 抽象调度器
 *
 * @author Verdient。
 */
abstract class AbstractDispatcher extends MessageQueueAbstractDispatcher
{
    /**
     * @author Verdient。
     */
    public function __construct()
    {
        parent::__construct($this->adapter());
    }

    /**
     * 获取适配器
     *
     * @author Verdient。
     */
    abstract protected function adapter(): AdapterInterface;
}
