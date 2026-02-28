<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Override;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;
use Verdient\Hyperf3\Di\Container;
use Verdient\MessageQueue\AdapterInterface;
use Verdient\MessageQueue\MessageInterface;

/**
 * AMQP数据包
 *
 * @author Verdient。
 */
class AmqpPacket extends AbstractMessage
{
    /**
     * @param MessageInterface $message 消息
     * @param AMQPMessage $amqpMessage AMQP消息
     *
     * @author Verdient。
     */
    public function __construct(
        public readonly MessageInterface $message,
        public readonly AMQPMessage $amqpMessage
    ) {}

    /**
     * @author Verdient。
     */
    #[Override]
    protected function adapter(): AdapterInterface
    {
        return Container::get(AmqpAdapter::class);
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function queue(): string
    {
        return $this->message->queue();
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function handle(): void
    {
        if ($this->message instanceof AbstractMessage) {
            $this->setLogger($this->message->logger());
        }

        $this->message->handle();
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function retriable(Throwable $throwable): bool
    {
        return $this->message->retriable($throwable);
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function push(): bool
    {
        return $this->message->push();
    }
}
