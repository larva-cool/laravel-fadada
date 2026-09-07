<?php

namespace Larva\Fadada\Services;

use Larva\Fadada\AccessToken;

/**
 * 业务模块 Service 基类
 *
 * 职责：
 *  1. 持有一个原 SDK 的 XxxClient 实例；
 *  2. 通过 __call 把方法转发到 XxxClient，并自动注入 accessToken；
 *  3. 当调用方传入关联数组作为参数时，自动通过反射用 setXxx() 给 req 对象赋值。
 *
 * 子类只需要在构造时把对应的原 SDK client 传进来即可。
 */
abstract class BaseService
{
    /**
     * 原 SDK 的业务 Client 实例（UserClient / CorpClient / ...）
     *
     * @var object
     */
    protected $client;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    public function __construct($client, AccessToken $accessToken)
    {
        $this->client      = $client;
        $this->accessToken = $accessToken;
    }

    /**
     * 获取 config 中配置的 Initiator（调用方主体）。
     *
     * 返回结构：
     *   [
     *     'idType'     => 'corp' | 'person',
     *     'openId' => '...',
     *   ]
     *
     * @return array{idType:string,openId:string}
     */
    public function getInitiator(): array
    {
        $default = ['idType' => 'corp', 'openId' => ''];
        $cfg = $this->config('fadada.initiator', $default);

        if (! is_array($cfg)) {
            $cfg = $default;
        }

        return [
            'idType'     => (string) ($cfg['idType'] ?? 'corp'),
            'openId' => (string) ($cfg['openId'] ?? ''),
        ];
    }

    /**
     * 读取配置：优先使用 Laravel config()，未安装则回退到 getenv()。
     */
    protected function config(string $key, $default = null)
    {
        if (function_exists('config')) {
            // @phpstan-ignore-next-line
            return \config($key, $default);
        }
        return getenv($key) ?: $default;
    }

    /**
     * 代理转发：
     *   - 第一个参数是 req 数据（数组、stdClass 或 req 对象）；
     *   - 剩余参数透传给原方法（极少数接口会用到）。
     *
     * 自动注入 accessToken。
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (! method_exists($this->client, $method)) {
            throw new \BadMethodCallException(sprintf(
                'Method [%s] does not exist on %s or its underlying SDK client.',
                $method,
                static::class
            ));
        }

        $token = $this->accessToken->getToken();

        if (empty($args)) {
            return $this->client->{$method}($token);
        }

        $first = array_shift($args);

        // 如果调用方已经传了 req 对象 / 原生 SDK 对象，直接转发
        if (is_object($first)) {
            array_unshift($args, $first);
            array_unshift($args, $token);
            return $this->client->{$method}(...$args);
        }

        // 否则把它当作关联数组或属性列表 -> 通过反射构造 req 对象
        $req = $this->buildRequest($method, $args[0] ?? [], $first);

        array_shift($args); // 移除已经处理过的第一个参数
        array_unshift($args, $req);
        array_unshift($args, $token);

        return $this->client->{$method}(...$args);
    }

    /**
     * 通过反射获取原 SDK 方法签名中的第一个 req 参数类型，
     * 把数组 / stdClass 转成对应的 req 对象并赋值。
     *
     * @param string $method
     * @param array  $extraArgs
     * @param mixed  $payload
     * @return object
     */
    protected function buildRequest($method, $extraArgs, $payload)
    {
        $ref = new \ReflectionMethod($this->client, $method);
        $parameters = $ref->getParameters();

        // 默认第一个参数就是 req 对象
        if (! isset($parameters[1])) {
            return $payload;
        }

        $reqType = $parameters[1]->getType();
        if ($reqType === null || $reqType->isBuiltin()) {
            return $payload;
        }

        $reqClass = $reqType->getName();

        // 调用方传入的已经是原 req 对象
        if ($payload instanceof $reqClass) {
            return $payload;
        }

        $req = new $reqClass();

        // stdClass -> array
        $data = is_array($payload) ? $payload : (array) $payload;

        $this->hydrate($req, $data);

        return $req;
    }

    /**
     * 用 setXxx() 或 public 属性给对象赋值。
     */
    protected function hydrate(object $object, array $data): void
    {
        foreach ($data as $key => $value) {
            // snake_case -> camelCase，例如 client_user_id -> clientUserId
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key))));

            $setter = 'set' . ucfirst($camel);

            if (method_exists($object, $setter)) {
                $object->{$setter}($value);
                continue;
            }

            if (property_exists($object, $camel)) {
                $object->{$camel} = $value;
                continue;
            }

            if (property_exists($object, $key)) {
                $object->{$key} = $value;
            }
        }
    }
}
