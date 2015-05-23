<?php

namespace rest;

use rest\exception\RestException;
use rest\storage\Base;
use rest\storage\StorageInterface;
use Slim\Slim;

/**
 * 扩展slim将核心，使其支持restful风格。
 * 参考文章：
 *
 * https://github.com/bolasblack/http-api-guide#user-content-http-%E5%8D%8F%E8%AE%AE
 * http://www.ruanyifeng.com/blog/2014/05/restful_api.html
 */
class Core extends Slim
{

    /**
     * @var string 当前请求的路径
     */
    public $resourcePath = '';

    /**
     * @var string 请求的资源名称
     */
    public $resourceName = '';

    /**
     * @var int 当前请求的资源ID
     */
    public $resourceID = null;

    /**
     * @var mixed 当前的meta数据
     */
    public $meta;

    /**
     * @var StorageInterface|Base
     */
    public $storage;

    /**
     * @var array 请求参数
     */
    public $query = [];

    /**
     * @var array 提交的数据
     */
    public $data = [];

    /**
     * @var array 一些行为参数
     */
    public $behavior = [
        '_pretty' => false,
        '_limit'  => 30,
        '_offset' => 0,
        '_sort'   => ['id' => 'DESC'],
    ];

    /**
     * 加载和解析资源路径
     *
     * @param $resource
     * @throws \rest\exception\MetaException
     * @throws \rest\exception\RestException
     */
    public function loadResource($resource = null)
    {
        if ($resource === null)
        {
            return;
        }

        $this->resourcePath = $resource;
        $resource = explode('/', $resource);

        // 如果最后一位是数字，那么就当做为ID处理
        if (is_numeric($resource[count($resource) - 1]))
        {
            $this->resourceID = array_pop($resource);
        }

        // 解析得出资源名称
        $this->resourceName = implode('/', $resource);

        // 获取得到meta的配置信息
        $this->meta = Meta::get($this->resourceName);
        if ( ! $this->meta)
        {
            $body = [
                'code'    => 404,
                'message' => 'The requested resource not found.'
            ];
            $this->restResponse($body, 404);
            return;
        }

        // 根据meta信息加载storage
        if ( ! isset($this->meta['storage']))
        {
            throw new RestException('Unable to locate the storage.');
        }
        $this->storage = Storage::instance($this->meta['storage']);
        //unset($this->meta['storage']);

        $this->storage->meta =& $this->meta;
    }

    /**
     * 加载query参数
     */
    public function loadQuery()
    {
        foreach ($this->request->get() as $k => $v)
        {
            if ($k{0} != '_')
            {
                $this->query[$k] = $v;
            }
        }
    }

    /**
     * 加载数据，POST/PUT/PATCH会用到
     */
    public function loadData()
    {
        if ($this->request->post())
        {
            $this->data = $this->request->post();
        }
    }

    /**
     * 加载行为逻辑
     */
    public function loadBehavior()
    {
        foreach ($this->request->get() as $k => $v)
        {
            if ($k{0} == '_')
            {
                $this->behavior[$k] = $v;
            }
        }
    }

    /**
     * 根据HTTP方法分派要执行的函数
     *
     * @param null $method
     */
    public function dispatchMethod($method = null)
    {
        if ( ! $this->meta)
        {
            return;
        }
        if ($method === null)
        {
            $method = strtolower($this->request->getMethod());
        }

        $methodConfig = $this->meta['method'];

        if (isset($methodConfig[$method]) && $methodConfig[$method])
        {
            switch ($method)
            {
                case 'get':
                    $this->resetGet();
                    break;
                case 'post':
                    $this->restPost();
                    break;
                case 'put':
                    $this->restPut();
                    break;
                case 'patch':
                    $this->restPatch();
                    break;
                case 'delete':
                    $this->restDelete();
                    break;
                case 'head':
                    $this->restHead();
                    break;
                case 'options':
                    $this->restOptions();
                    break;
                default:
                    $this->restResponse(['message' => 'Unknown method.']);
            }
        }
        else
        {
            $body = [
                'code'    => 405,
                'message' => 'The requested method not allowed.'
            ];
            $this->restResponse($body, 405);
        }
    }

    /**
     * 返回json数据
     *
     * @param     $body
     * @param int $code
     */
    public function restResponse($body, $code = 200)
    {
        if ($this->behavior['_pretty'])
        {
            $content = json_encode($body, JSON_PRETTY_PRINT);
        }
        else
        {
            $content = json_encode($body);
        }

        $this->response->setStatus($code);
        $this->response->headers['content-type'] = 'application/json';
        $this->response->setBody($content);
    }

    /**
     * GET（SELECT）：从服务器取出资源（一项或多项）。
     *
     * - 完成请求后返回状态码 200 OK
     * - 完成请求后需要返回被请求的资源详细信息
     */
    public function resetGet()
    {
        // 有ID的情况下，获取单条记录
        if (is_numeric($this->resourceID))
        {
            $result = $this->restGetOne();
        }
        else
        {
            $result = $this->restGetMulti();
        }

        if ( ! $result)
        {
            $body = [
                'code'    => 404,
                'message' => 'The requested record not found.'
            ];
            $this->restResponse($body, 404);
        }
        else
        {
            $this->restResponse($result);
        }
    }

    /**
     * 获取单条记录
     *
     * @return mixed
     */
    public function restGetOne()
    {
        $result = $this->storage->record(['id' => $this->resourceID], 1);
        return array_shift($result);
    }

    /**
     * 获取多条记录
     *
     * @return mixed
     */
    public function restGetMulti()
    {
        return $this->storage->record(
            $this->query,
            $this->behavior['_limit'],
            $this->behavior['_offset'],
            $this->behavior['_sort']
        );
    }

    /**
     * POST（CREATE）：在服务器新建一个资源。
     *
     * - 创建完成后返回状态码 201 Created
     * - 完成请求后需要返回被创建的资源详细信息
     */
    public function restPost()
    {

    }

    /**
     * PUT（UPDATE）：用于完整的替换资源或者创建指定身份的资源，比如创建 id 为 123 的某个资源，客户端需要传完整的属性。
     *
     * - 如果是创建了资源，则返回 201 Created
     * - 如果是替换了资源，则返回 200 OK
     * - 完成请求后需要返回被修改的资源详细信息
     */
    public function restPut()
    {
    }

    /**
     * PATCH（UPDATE）：在服务器更新资源（客户端提供改变的属性），完成请求后返回200，并返回被修改的资源详细信息。
     */
    public function restPatch()
    {
        $result = [
            'name' => 'New Name'
        ];

        $this->restResponse([$result]);
    }

    /**
     * 从服务器删除资源。完成请求后返回状态码 204 No Content
     */
    public function restDelete()
    {
        $this->restResponse('', 204);
    }

    /**
     * 检测指定的资源是否存在存在则200，否则404
     */
    public function restHead()
    {
        $data = $this->restGetOne();

        if ($data)
        {
            $code = 200;
        }
        else
        {
            $code = 404;
        }
        $this->restResponse(null, $code);
    }

    /**
     * 获取资源的元数据，例如资源的名称、支持的HTTP方法、字段、描述和其他静态信息
     */
    public function restOptions()
    {
        $result = [
            'name'        => 'Resource Name',
            'description' => 'This is a resource',
            'methods'     => [
                'GET',
                'POST'
            ],
            'columns'     => [
                'id',
                'name',
                'description',
            ],
        ];

        $this->restResponse($result);
    }
}
