<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\MessageQueue;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;

use function Hyperf\Config\config;

/**
 * AMQP连接管理器
 *
 * @author Verdient。
 */
class AmqpConnectionManager
{
    /**
     * @var array<int,array<string,AbstractConnection>> 缓存的连接
     *
     * @author Verdient。
     */
    protected static array $connections = [];

    /**
     * 创建连接
     *
     * @param string $pool 连接池名称
     *
     * @author Verdient。
     */
    protected static function create(string $pool): AbstractConnection
    {
        $config = config('amqp.' . $pool);

        $connectionConfig = new AMQPConnectionConfig();
        $connectionConfig->setHost($config['host']);
        $connectionConfig->setPort($config['port']);
        $connectionConfig->setUser($config['user']);
        $connectionConfig->setPassword($config['password']);
        $connectionConfig->setVhost($config['vhost']);

        $params = $config['params'];

        $connectionConfig->setInsist($params['insist']);
        $connectionConfig->setLoginMethod($params['login_method']);
        $connectionConfig->setLocale($params['locale']);
        $connectionConfig->setConnectionTimeout($params['connection_timeout']);
        $connectionConfig->setReadTimeout($params['read_write_timeout']);
        $connectionConfig->setWriteTimeout($params['read_write_timeout']);
        $connectionConfig->setHeartbeat($params['heartbeat']);
        $connectionConfig->setKeepAlive($params['keepalive']);
        $connectionConfig->setChannelRpcTimeout($params['channel_rpc_timeout']);

        if (!empty($params['context'])) {
            $connectionConfig->setStreamContext($params['context']);
        }

        if ($params['login_response'] !== null) {
            $connectionConfig->setLoginResponse($params['login_response']);
        }

        return AMQPConnectionFactory::create($connectionConfig);
    }

    /**
     * 获取连接对象
     *
     * @author Verdient。
     */
    public static function get(string $pool): AbstractConnection
    {
        $pid = getmypid();

        if (!isset(static::$connections[$pid])) {
            static::$connections[$pid] = [];
        }

        foreach (array_diff(array_keys(static::$connections), [$pid]) as $needlessPid) {
            unset(static::$connections[$needlessPid]);
        }

        if (!isset(static::$connections[$pid][$pool])) {
            static::$connections[$pid][$pool] = static::create($pool);
        }

        return static::$connections[$pid][$pool];
    }
}
