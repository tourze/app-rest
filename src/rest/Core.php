<?php

namespace rest;

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
    public $resourceID = 0;

    /**
     * 加载和解析资源路径
     *
     * @param $resource
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
        $this->resourceName = implode('_', $resource);

        // 获取得到meta的配置信息

    }

    /**
     * 根据HTTP方法分派要执行的函数
     *
     * @param null $method
     */
    public function dispatchMethod($method = null)
    {
        if ($method === null)
        {
            $method = $this->request->getMethod();
        }

        switch ($method)
        {
            case 'GET':
                $this->resetGet();
                break;
            case 'POST':
                $this->restPost();
                break;
            case 'PUT':
                $this->restPut();
                break;
            case 'PATCH':
                $this->restPatch();
                break;
            case 'DELETE':
                $this->restDelete();
                break;
            case 'HEAD':
                $this->restHead();
                break;
            case 'OPTIONS':
                $this->restOptions();
                break;
            default:
                $this->restResponse(['message' => 'Unknown method.']);
        }
    }

    public function restResponse($body, $code = 200)
    {
        $this->response->setStatus($code);
        $this->response->headers['content-type'] = 'application/json';
        echo json_encode($body);
    }

    /**
     * GET（SELECT）：从服务器取出资源（一项或多项）。
     *
     * - 完成请求后返回状态码 200 OK
     * - 完成请求后需要返回被请求的资源详细信息
     */
    public function resetGet()
    {
        if ($this->resourceID)
        {
            $faker = \Faker\Factory::create();
            $result = [
                'id' => $faker->randomDigitNotNull,
                'name' => $faker->name,
                'address' => $faker->address,
            ];
        }
        else
        {
            $records = [];
            $i = 1;
            $max = rand(10, 15);
            while ($i <= $max)
            {
                $faker = \Faker\Factory::create();
                $records[] = [
                    'id' => $faker->randomDigitNotNull,
                    'name' => $faker->name,
                    'address' => $faker->address,
                ];
                $i++;
            }

            $result = $records;
        }

        $this->restResponse($result);
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
     * 确定字段或元数据是否存在，存在则200，否则404
     */
    public function restHead()
    {
        $this->restResponse([], 404);
    }

    /**
     * 获取资源的元数据，例如资源的名称、支持的HTTP方法、字段、描述和其他静态信息
     */
    public function restOptions()
    {
        $result = [
            'name' => 'Resource Name',
            'description' => 'This is a resource',
            'methods' => [
                'GET',
                'POST'
            ],
            'columns' => [
                'id',
                'name',
                'description',
            ],
        ];

        $this->restResponse($result);
    }
}
