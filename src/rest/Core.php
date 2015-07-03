<?php

namespace rest;

use rest\exception\RestException;
use rest\Storage\Base;
use rest\Storage\StorageInterface;
use tourze\Base\Helper\Arr;
use tourze\Base\Object;
use tourze\Http\Request;
use tourze\Http\Response;
use tourze\Route\Route;

/**
 * restful风格核心类
 * 参考文章：
 *
 * https://github.com/bolasblack/http-api-guide#user-content-http-%E5%8D%8F%E8%AE%AE
 * http://www.ruanyifeng.com/blog/2014/05/restful_api.html
 */
class Core extends Object
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
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var mixed 当前的meta数据
     */
    public $meta;

    /**
     * @var StorageInterface|Base
     */
    public $storage;

    /**
     * @var Cache
     */
    public $cache;

    /**
     * @var Logic
     */
    public $logic;

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
            $this->restError('The requested resource not found.', 404);
            return;
        }

        // 根据meta信息加载storage
        if ( ! isset($this->meta['storage']))
        {
            throw new RestException('Unable to locate the storage.');
        }
        $this->storage = Storage::instance($this->meta['storage']);
        $this->storage->app =& $this;

        // 缓存
        $this->cache = new Cache;
    }

    /**
     * 加载query参数
     */
    public function loadQuery()
    {
        foreach ($this->request->query() as $k => $v)
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
        foreach ($this->request->query() as $k => $v)
        {
            if ($k{0} == '_')
            {
                $this->behavior[$k] = $v;
            }
        }
    }

    /**
     * 获取http方法配置
     *
     * @return array
     */
    public function getMetaMethodConfig()
    {
        if ( ! isset($this->meta['method']))
        {
            return [
                'get' => true,
            ];
        }

        return $this->meta['method'];
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
            $method = strtolower($this->request->method);
        }

        //$this->response->headers['X-Powered-By'] = 'Rest Server';
        $methodConfig = $this->getMetaMethodConfig();

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
                    $this->restError('Unknown method.');
            }
        }
        else
        {
            $this->restError('The requested method not allowed.', 405);
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
        if ( ! isset($this->behavior['_format']))
        {
            $this->behavior['_format'] = 'json';
        }

        switch ($this->behavior['_format'])
        {
            case 'json':
                if ($this->behavior['_pretty'])
                {
                    $content = json_encode($body, JSON_PRETTY_PRINT);
                }
                else
                {
                    $content = json_encode($body);
                }
                $contentType = 'application/json';
                break;

            case 'jsonp':
                if ($this->behavior['_pretty'])
                {
                    $content = json_encode($body, JSON_PRETTY_PRINT);
                }
                else
                {
                    $content = json_encode($body);
                }
                $content = Arr::get($this->behavior, '_callback') . '(' . $content . ')';

                $contentType = 'application/json';
                break;

            default:
                if ($this->behavior['_pretty'])
                {
                    $content = json_encode($body, JSON_PRETTY_PRINT);
                }
                else
                {
                    $content = json_encode($body);
                }
                $contentType = 'application/json';
        }

        $this->response->status = $code;
        $this->response->headers('Content-Type', $contentType);
        $this->response->body = $content;
    }

    /**
     * REST报错
     *
     * @param     $message
     * @param int $code
     */
    public function restError($message, $code = 500)
    {
        $body = [
            'code'    => $code,
            'message' => $message
        ];
        $this->response->headers('Rest-Message', $message);
        $this->restResponse($body, $code);
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
            $this->restError('The requested record not found.', 404);
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
        // 先尝试读缓存
        $key = str_replace(['/', '\\'], '_', $this->resourceName) . ':' . $this->resourceID;
        if ($this->storage->cache && $result = $this->cache->get($key))
        {
            $result = json_decode($result, true);
        }
        else
        {
            // 缓存读取失败
            // 从storage读取
            $result = $this->storage->record(['id' => $this->resourceID], 1);
            $result = array_shift($result);

            // 保存到缓存
            if ($result)
            {
                $this->cache->set($key, json_encode($result), 60 * 5);
            }
        }

        // 返回结果
        return $result;
    }

    /**
     * 获取多条记录
     *
     * @return mixed
     */
    public function restGetMulti()
    {
        $queryHash = json_encode([
            $this->query,
            (int) $this->behavior['_limit'],
            (int) $this->behavior['_offset'],
            $this->behavior['_sort']
        ]);
        $queryHash = sha1($queryHash);
        $key = str_replace(['/', '\\'], '_', $this->resourceName) . ':list:' . $queryHash;

        if ($this->storage->cache && $result = $this->cache->get($key))
        {
            $result = json_decode($result, true);
        }
        else
        {
            // 缓存读取失败
            // 从storage读取
            $result = $this->storage->record(
                $this->query,
                $this->behavior['_limit'],
                $this->behavior['_offset'],
                $this->behavior['_sort']
            );

            // 保存到缓存
            if ($result)
            {
                $this->cache->set($key, json_encode($result), 60 * 5);
            }
        }

        return $result;
    }

    /**
     * POST（CREATE）：在服务器新建一个资源。
     *
     * - 创建完成后返回状态码 201 Created
     * - 完成请求后需要返回被创建的资源详细信息
     */
    public function restPost()
    {
        $result = $this->storage->create($this->data);

        if ($result)
        {
            $result = array_shift($result);
            // 创建成功
            $this->resourceID = $result['id'];
            // 返回资源信息
            $this->response->headers('Location', Route::url('rest', ['resource' => $this->resourcePath . '/' . $result['id']]));
            $this->restResponse($result, 201);
        }
        else
        {
            // 创建失败
            $this->restError('Error occurred while creating object.');
        }
    }

    /**
     * PUT（UPDATE）：用于完整的替换资源或者创建指定身份的资源，比如创建 id 为 123 的某个资源，客户端需要传完整的属性。
     * 要谨慎使用PUT方法
     *
     * - 如果是创建了资源，则返回 201 Created
     * - 如果是替换了资源，则返回 200 OK
     * - 完成请求后需要返回被修改的资源详细信息
     */
    public function restPut()
    {
        // 如果指定的资源ID查找不到，那么当前操作就是创建资源
        if ( ! $data = $this->restGetOne())
        {
            // 指定主键
            $this->data['id'] = $this->resourceID;
            // 逻辑跟post一样，所以直接调用post方法
            $this->restPost();
            return;
        }

        // 更新资源
        $result = $this->storage->update($data['id'], $this->data);

        if ($result)
        {
            // 删除缓存
            $key = str_replace(['/', '\\'], '_', $this->resourceName) . ':' . $this->resourceID;
            $this->cache->del($key);
            // 重新获取一次
            $record = $this->restGetOne();
            $this->restResponse($record, 201);
        }
        else
        {
            // 更新失败
            $this->restError('Error occurred while modifying object.');
        }
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
        // 获取要删除的资源
        $data = $this->restGetOne();
        if ( ! $data)
        {
            $this->restError('The requested record not found.', 404);
            return;
        }

        if ($this->storage->delete($data['id']))
        {
            // 删除缓存
            $key = str_replace(['/', '\\'], '_', $this->resourceName) . ':' . $this->resourceID;
            $this->cache->del($key);
            $this->restError('The record was removed.', 204);
        }
        else
        {
            $this->restError('Error occurred while deleting object.', 204);
        }
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
        $data = $this->meta;
        unset($data['storage']);

        $this->restResponse($data);
    }
}
