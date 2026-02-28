<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Override;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;
use Throwable;
use Verdient\MessageQueue\AdapterInterface;
use Verdient\MessageQueue\MessageInterface;

use function Hyperf\Config\config;

/**
 * AMQP适配器
 *
 * @author Verdient。
 */
class AmqpAdapter implements AdapterInterface
{
    /**
     * 连接池名称
     *
     * @author Verdient。
     */
    protected string $pool;

    /**
     * 已声明的队列名称
     *
     * @author Verdient。
     */
    protected array $declaredQueues = [];

    /**
     * 推送通道对象
     *
     * @author Verdient。
     */
    protected ?AMQPChannel $pushChannel = null;

    /**
     * 拉取通道
     *
     * @author Verdient。
     */
    protected ?AMQPChannel $popChannel = null;

    /**
     * @author Verdient。
     */
    public function __construct(?string $pool = null)
    {
        if ($pool === null) {
            $pools = array_keys(config('amqp', []));
            if (empty($pools)) {
                throw new RuntimeException('No amqp connection pool was found.');
            }
            $this->pool = in_array('default', $pools) ? 'default' : $pools[0];
        } else {
            $this->pool = $pool;
        }
    }

    /**
     * @author Verdient。
     */
    public function __serialize(): array
    {
        return [
            'pool' => $this->pool,
            'pushChannel' => null,
            'popChannel' => null
        ];
    }

    /**
     * 获取推送通道
     *
     * @author Verdient。
     */
    protected function pushChannel(): AMQPChannel
    {
        if ($this->pushChannel === null) {
            $connection = AmqpConnectionManager::get($this->pool);
            $this->pushChannel = $connection->channel();
            $this->pushChannel->confirm_select();
        }

        return $this->pushChannel;
    }

    /**
     * 获取拉取通道
     *
     * @author Verdient。
     */
    protected function popChannel(): AmqpChannel
    {
        if ($this->popChannel === null) {
            $connection = AmqpConnectionManager::get($this->pool);
            $this->popChannel = $connection->channel();
            $this->popChannel->basic_qos(null, 1, null);
        }

        return $this->popChannel;
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
     * @author Verdient。
     */
    #[Override]
    public function push(MessageInterface $message): bool
    {
        $queue = '(AMQP default)-' . ucfirst($message->queue());

        $payload = serialize($message);

        $amqpMessage = new AMQPMessage(
            $payload,
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/php-serialize'
            ]
        );

        $channel = $this->pushChannel();

        $channel->basic_publish(
            $amqpMessage,
            '',
            $queue,
            true
        );

        $channel->wait_for_pending_acks_returns();

        return true;
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function pop(string $queue): ?MessageInterface
    {
        $queue = '(AMQP default)-' . ucfirst($queue);

        $channel = $this->popChannel();

        if (!isset($this->declaredQueues[$queue])) {
            $channel->queue_declare(
                $queue,
                false,
                true,
                false,
                false
            );

            $this->declaredQueues[$queue] = true;
        }

        if (!$AMQPMessage = $channel->basic_get($queue, true)) {
            return null;
        }

        $body = $AMQPMessage->getBody();

        $message = @unserialize($body);

        if (!$message instanceof MessageInterface) {
            $channel->basic_nack($AMQPMessage->getDeliveryTag());
            return null;
        }

        return new AmqpPacket($message, $AMQPMessage);
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
        if ($message instanceof AmqpPacket) {
            if (!$message->message->push()) {
                throw new RuntimeException('Message ' . $message->message::class . ' failed to retry and could not be pushed into the queue.');
            }
        } else {
            throw new RuntimeException('$message must be ' . AmqpPacket::class . ', ' . $message::class . ' given.');
        }
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function fault(MessageInterface $message, Throwable $throwable): void {}
}
