<?php

namespace Larva\Fadada\Services;

use FddCloud\client\DocClient;
use Larva\Fadada\AccessToken;

/**
 * 文档处理（文件上传、文件处理、OFD 追加、文档验签等）
 *
 * @see \FddCloud\client\DocClient
 */
class DocService extends BaseService
{
    public function __construct(DocClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
