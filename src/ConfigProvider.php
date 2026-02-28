<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Verdient\Hyperf3\MessageQueue\Annotation\MessageQueueCollector;
use Verdient\MessageQueue\MessageInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for message queue.',
                    'source' => dirname(__DIR__) . '/publish/message_queue.php',
                    'destination' => constant('BASE_PATH') . '/config/autoload/message_queue.php',
                ]
            ],
            'listeners' => [
                RegisterLoggerRuleListener::class => 101,
                RegisterMessageQueueListener::class => 100
            ],
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        MessageQueueCollector::class
                    ]
                ],
            ],
            'logger' => [
                MessageInterface::class => fn($name) => Utils::generateLoggerConfig($name)
            ]
        ];
    }
}
