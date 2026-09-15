<?php

namespace Larva\Fadada\Services;

use FddCloud\client\CallbackClient;
use FddCloud\utils\crypt\FddCryptUtil;
use Larva\Fadada\AccessToken;
use RuntimeException;

/**
 * 回调管理
 *
 * 提供两类能力：
 *  1. 出站：查询法大大平台的回调推送记录（getCallbacks）
 *  2. 入站：接收法大大回调时的验签、解析、响应（verifySignature / parseCallback / handleCallback / successResponse）
 *
 * @see \FddCloud\client\CallbackClient
 */
class CallbackService extends BaseService
{
    /**
     * 回调时间戳容差（毫秒），法大大官方要求 ±5 分钟。
     */
    public const TIMESTAMP_TOLERANCE = 300000;

    public function __construct(CallbackClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }

    /**
     * 查询回调列表。
     *
     * @param  int  $pageNo   页码（从 1 起）
     * @param  int  $pageSize 每页数量
     * @param  array<string, mixed>  $filters  其他过滤条件
     * @return array<string, mixed>
     */
    public function getCallbacks(int $pageNo = 1, int $pageSize = 20, array $filters = []): array
    {
        $payload = array_merge($filters, [
            'openCorpId' => $this->getInitiator()['openId'],
            'pageNo'     => $pageNo,
            'pageSize'   => $pageSize,
        ]);

        return $this->getCallbackList($payload);
    }

    /**
     * 验证回调签名。
     *
     * 法大大回调时携带 X-FASC-Sign 签名头，使用 HMAC-SHA256 算法。
     * 本方法复用官方 SDK 的 FddCryptUtil::signature() 计算期望签名，
     * 并以固定时间比较（hash_equals）防止时序攻击。
     *
     * @param  array<string, string>  $headers    HTTP 请求头（键名大小写不敏感）
     * @param  string                 $bizContent  回调 body 中的 bizContent 原始字符串
     * @return bool  验签通过返回 true
     */
    public function verifySignature(array $headers, string $bizContent): bool
    {
        $h = $this->normalizeHeaders($headers);

        $sign = $h['x-fasc-sign'] ?? '';
        if ($sign === '') {
            return false;
        }

        $timestamp  = $h['x-fasc-timestamp'] ?? '';
        $appSecret   = (string) $this->config('fadada.app_secret', '');

        if ($timestamp === '' || $appSecret === '') {
            return false;
        }

        // 参与签名的参数（法大大官方规范）
        $params = [
            'X-FASC-App-Id'     => $h['x-fasc-app-id'] ?? '',
            'X-FASC-Sign-Type'  => $h['x-fasc-sign-type'] ?? 'HMAC-SHA256',
            'X-FASC-Timestamp'  => $timestamp,
            'X-FASC-Nonce'      => $h['x-fasc-nonce'] ?? '',
            'X-FASC-Event'      => $h['x-fasc-event'] ?? '',
            'bizContent'         => $bizContent,
        ];

        $computed = (new FddCryptUtil())->signature($timestamp, $appSecret, $params);

        return hash_equals($sign, $computed);
    }

    /**
     * 验证时间戳新鲜度（防重放攻击）。
     *
     * 法大大要求回调时间戳与服务器当前时间正负不超过 5 分钟（300000 毫秒）。
     *
     * @param  string  $timestamp  毫秒级 Unix 时间戳
     * @param  int     $tolerance  容差（毫秒），默认 300000
     * @return bool
     */
    public function isTimestampValid(string $timestamp, int $tolerance = self::TIMESTAMP_TOLERANCE): bool
    {
        $ts = (int) $timestamp;
        if ($ts <= 0) {
            return false;
        }

        $now = (int) round(microtime(true) * 1000);

        return abs($now - $ts) <= $tolerance;
    }

    /**
     * 解析回调请求（验签 + 解码 bizContent）。
     *
     * 验签通过后，将 bizContent JSON 解码为数组并连同事件信息一起返回。
     * 验签失败抛出 RuntimeException。
     *
     * @param  array<string, string>  $headers
     * @param  string                 $bizContent
     * @return array{event: string, data: array<string, mixed>, timestamp: string, nonce: string, appId: string}
     * @throws RuntimeException  验签失败时抛出
     */
    public function parseCallback(array $headers, string $bizContent): array
    {
        if (! $this->verifySignature($headers, $bizContent)) {
            throw new RuntimeException('法大大回调签名验证失败');
        }

        $h = $this->normalizeHeaders($headers);
        $data = json_decode($bizContent, true);

        return [
            'event'     => $h['x-fasc-event'] ?? '',
            'data'      => is_array($data) ? $data : [],
            'timestamp' => $h['x-fasc-timestamp'] ?? '',
            'nonce'     => $h['x-fasc-nonce'] ?? '',
            'appId'     => $h['x-fasc-app-id'] ?? '',
        ];
    }

    /**
     * 处理回调请求（一站式：时间戳校验 + 验签 + 解析）。
     *
     * 依次执行：
     *  1. 时间戳新鲜度校验（防重放）
     *  2. 签名验证（防篡改）
     *  3. bizContent JSON 解码
     *
     * 任何一步失败均抛出 RuntimeException，宿主应用可 catch 后返回非 success 响应。
     *
     * @param  array<string, string>  $headers
     * @param  string                 $bizContent
     * @return array{event: string, data: array<string, mixed>, timestamp: string, nonce: string, appId: string}
     * @throws RuntimeException
     */
    public function handleCallback(array $headers, string $bizContent): array
    {
        $h = $this->normalizeHeaders($headers);
        $timestamp = $h['x-fasc-timestamp'] ?? '';

        if (! $this->isTimestampValid($timestamp)) {
            throw new RuntimeException('法大大回调时间戳已过期');
        }

        return $this->parseCallback($headers, $bizContent);
    }

    /**
     * 返回法大大要求的成功响应。
     *
     * 法大大判定回调成功的条件：HTTP 200 且 body 中包含 "success"。
     * 宿主应用在处理完回调业务逻辑后调用本方法返回响应即可。
     *
     * @return string  JSON 字符串 {"msg":"success"}
     */
    public function successResponse(): string
    {
        return json_encode(['msg' => 'success']);
    }

    /**
     * 获取配置的回调地址。
     *
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return (string) $this->config('fadada.callback_url', '');
    }

    /**
     * 将 HTTP 请求头键名统一转为小写，便于大小写不敏感查找。
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            // Symfony HeaderBag::all() 返回值为数组（如 ['application/json']），
            // 此处统一取出第一个元素转为字符串。
            if (is_array($value)) {
                $value = $value[0] ?? '';
            }
            $normalized[strtolower($key)] = (string) $value;
        }

        return $normalized;
    }
}
