<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use Hyperf\Stringable\Str;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Verdient\MessageQueue\AdapterInterface;

use function Hyperf\Support\make;

/**
 * 工具
 *
 * @author Verdient。
 */
class Utils
{
    /**
     * 创建适配器
     *
     * @author Verdient。
     */
    public static function createAdapter(string|array|null $config): AdapterInterface
    {
        if (is_string($config)) {
            return make($config);
        }

        if (empty($config)) {
            throw new InvalidArgumentException('Job adapter config is empty');
        }

        $class = array_key_first($config);

        return make($class, $config[$class]);
    }

    /**
     * 简化名称
     *
     * @param string $class 类名
     *
     * @author Verdient。
     */
    protected static function simplifyName(string $class): string
    {
        $namespaces = [
            'App\MessageQueues\\',
            'App\MessageQueue\\',
        ];

        foreach ($namespaces as $namespace) {
            if (str_starts_with($class, $namespace)) {
                $class = substr($class, strlen($namespace));
                break;
            }
        }

        $suffixes = [
            'MessageQueue',
            'Queue'
        ];

        foreach ($suffixes as $suffix) {
            $length = strlen($suffix);
            if (strlen($class) > $length && str_ends_with($class, $suffix)) {
                $class = substr($class, 0, -$length);
            }
        }

        return $class;
    }

    /**
     * 生成记录器配置
     *
     * @param string $name 类名
     *
     * @author Verdient。
     */
    public static function generateLoggerConfig(string $name): array
    {
        $nameParts = array_map([Str::class, 'kebab'], explode('\\', static::simplifyName($name)));

        $filename = BASE_PATH . '/runtime/logs/message-queue/' . implode('/', $nameParts) . '/.log';

        return [
            'handler' => [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $filename,
                    'filenameFormat' => '{date}'
                ],
            ],
            'formatter' => [
                'class' => LineFormatter::class,
                'constructor' => [
                    'format' => "%datetime% [%level_name%] %message%\n",
                    'dateFormat' => 'Y-m-d H:i:s',
                    'allowInlineLineBreaks' => true,
                ],
            ]
        ];
    }
}
