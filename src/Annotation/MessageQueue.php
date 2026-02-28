<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Override;
use TypeError;
use Verdient\Hyperf3\MessageQueue\AbstractDispatcher;

/**
 * 消息队列
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS)]
class MessageQueue extends AbstractAnnotation
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function collectClass(string $className): void
    {
        if (!is_subclass_of($className, AbstractDispatcher::class)) {
            throw new TypeError('The class ' . $className . ' with #[MessageQueue] must implement ' . AbstractDispatcher::class . '.');
        }

        MessageQueueCollector::collectClass($className, $this);
    }
}
