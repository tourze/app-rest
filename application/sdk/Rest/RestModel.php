<?php

namespace sdk\Rest;

use Doctrine\DBAL\Exception\TableNotFoundException;
use sdk\Base\Exception\BaseException;
use sdk\Base\Object;
use sdk\Http\Exception\Http404Exception;
use sdk\Mvc\Model\Model;
use sdk\Mvc\Model\Exception\ValidationException;
use sdk\Rest;
use sdk\Rest\Exception\RestException;
use sdk\Rest\Method\BasicMethod;
use sdk\Rest\Method\HeadMethod;
use stdClass;

/**
 * 基础的模型设置
 *
 * @property string modelDesc
 * @property string modelName
 * @property string modelTitle
 */
class RestModel extends Object implements RestCors, RestAuth, HeadMethod, BasicMethod
{

    /**
     * @var string 模型名称
     */
    protected $_modelName = '';

    /**
     * @var string 模型标题
     */
    protected $_modelTitle = '';

    /**
     * @var string  对当前模型的简短描述
     */
    protected $_modelDesc = '';

    /**
     * @var bool 是否检查模型名称是否有效
     */
    protected $_modelNameCheck = true;

    /**
     * @var string 当前模型
     */
    protected $_restModel = '';

    /**
     * @var bool 是否检查restModel的有效性
     */
    protected $_restModelCheck = true;

    /**
     * @var bool 是否启用授权认证，默认情况需要授权
     */
    protected $_enableAuth = false;

    /**
     * @var bool 运行使用GET方法
     */
    protected $_enableGet = true;

    /**
     * @var bool 运行获取单条记录
     */
    protected $_enableGetOne = true;

    /**
     * @var bool 允许一次获取多条记录
     */
    protected $_enableGetMulti = true;

    /**
     * @var bool 允许POST方法
     */
    protected $_enablePost = true;

    /**
     * @var bool 允许PUT方法
     */
    protected $_enablePut = true;

    /**
     * @var bool 允许DELETE方法
     */
    protected $_enableDelete = true;

    /**
     * @var bool 允许OPTIONS方法
     */
    protected $_enableOptions = true;

    /**
     * @var bool 允许HEAD方法
     */
    protected $_enableHead = true;

    /**
     * @var array 额外增加的cors数据
     */
    protected $_extraCorsData = [];

    /**
     * @var array  模型自动加载和调用的方法和参数
     */
    protected $_restModelCall = [];

    /**
     * @var int  默认情况下，列表只允许返回30条记录，不过可以通过传_all参数来强制返回全部
     */
    protected $_restGetMultiLimit = 30;

    /**
     * @var bool 是否允许在列表中一次性返回所有记录，默认是不允许的，只能传limit
     */
    protected $_restGetMultiAll = false;

    /**
     * 为解决模型的自定义调用而定制的方法
     *
     * @param      $instance
     * @param null $calls
     */
    protected function handleModelCall($instance, $calls = null)
    {
        if (null === $calls)
        {
            $calls = $this->_restModelCall;
        }
        $calls = (array) $calls;
        foreach ($calls as $call)
        {
            $method = $call[0];
            $params = $call[1];

            if ( ! is_array($method))
            {
                $method = [
                    $instance,
                    $method
                ];
            }
            if ( ! is_array($params))
            {
                $params = [$params];
            }
            call_user_func_array($method, $params);
        }
    }

    /**
     * 构造函数
     *
     * @param array $args
     *
     * @throws RestException
     */
    public function __construct($args = [])
    {
        parent::__construct($args);
        if ($this->_restModelCheck && ! $this->_restModel)
        {
            throw new RestException('The resource model is empty');
        }
        if ($this->_modelNameCheck && ! $this->_modelName)
        {
            throw new  RestException('The resource title is empty');
        }
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return $this->_modelName;
    }

    /**
     * 模型准备阶段
     *
     * @return Model
     */
    public function prepareModel()
    {
        return new $this->_restModel;
    }

    /**
     * 检验当前模型设置是否正确
     */
    public function valid()
    {
        return class_exists($this->modelName);
    }

    /**
     * 授权认证，默认只检查是否授权
     *
     * @param Rest|RestCore $rest
     *
     * @return bool
     */
    public function restAuth(Rest $rest)
    {
        // 检查是否需要授权，不需要授权的话，直接放行
        if ( ! $this->_enableAuth)
        {
            return true;
        }

        // 首先尝试从GET中获取
        $token = $rest->query('_token');

        // 尝试从header中获取
        if ( ! $token)
        {
            $token = $rest->request->headers('Authorization');
                $token = str_replace('Bearer ', '', $token);
        }

        if ( ! $token)
        {
            //return false;
            return true;
        }
        return true;
    }

    /**
     * 同源策略和跨域访问设置，默认为全部允许
     *
     * @param Rest|RestCore $rest
     */
    public function restCors(Rest $rest)
    {
        $allowMethods = [];
        if ($this->allowGet())
        {
            $allowMethods[] = 'GET';
        }
        if ($this->allowPost())
        {
            $allowMethods[] = 'POST';
        }
        if ($this->allowPut())
        {
            $allowMethods[] = 'PUT';
        }
        if ($this->allowDelete())
        {
            $allowMethods[] = 'DELETE';
        }
        if ($this->allowHead())
        {
            $allowMethods[] = 'HEAD';
        }
        if ($this->allowOptions())
        {
            $allowMethods[] = 'OPTIONS';
        }

        $origin = $rest->request->headers('Origin');
        $rest->cors([
            'methods'     => $allowMethods,
            'origin'      => $origin,
            'credentials' => 'true'
        ]);
    }

    /**
     * HEAD方法，主要用于判断元是否存在
     *
     * @param Rest $rest
     *
     * @return array
     * @throws RestException
     */
    public function restHead(Rest $rest)
    {
        if ( ! $this->_enableHead)
        {
            $rest->sendCode(400);
        }

        $rest->sendCode(200);
    }

    /**
     * @var  Model
     */
    protected $_restOptionsModel;

    /**
     * OPTIONS方法，可以用于返回可进行的操作
     *
     * @param Rest $rest
     *
     * @return string
     * @throws RestException
     */
    public function restOptions(Rest $rest)
    {
        if ( ! $this->_enableOptions)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'OPTIONS']));
        }

        $result = [];
        try
        {
            $this->_restOptionsModel = $this->prepareModel();

            $result['title'] = $this->modelTitle;
            $result['columns'] = RestDb::formatColumnConfig($this->_restOptionsModel->listColumns());

            // 整理一下
        }
        catch (TableNotFoundException $e)
        {
            // 表名不存在
            return [];
        }

        return $result;
    }

    /**
     * 从服务器取出资源（一项或多项）
     *
     * @param Rest $rest
     *
     * @return mixed
     */
    public function restGet(Rest $rest)
    {
        if ( ! $this->_enableGet)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'GET']));
        }

        // 获取单个资源
        if ($rest->param('id') && '_count' != $rest->param('id'))
        {
            return $this->restGetOne($rest);
        }
        // 获取多个资源
        else
        {
            return $this->restGetMulti($rest);
        }
    }

    /**
     * @var  Model
     */
    protected $_restGetOneModel;

    /**
     * @param Rest $rest
     *
     * @return mixed
     * @throws Http404Exception
     */
    protected function restGetOne(Rest $rest)
    {
        if ( ! $this->_enableGetOne)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'GET(single)']));
        }

        /** @var Model $model */
        $this->_restGetOneModel = $this->prepareModel();
        $this->_restGetOneModel->where($this->_restGetOneModel->primaryKey(), '=', $rest->param('id'));
        $this->handleModelCall($this->_restGetOneModel);
        $this->_restGetOneModel->find();

        if ( ! $this->_restGetOneModel->loaded())
        {
            $rest->sendCode(404, __('Resource not found, ID: :id', [
                ':id' => $rest->param('id')
            ]));
        }

        return $this->_restGetOneModel->asArray();
    }

    /**
     * @var  Model
     */
    protected $_restGetMultiModel;

    /**
     * 一次获取多条记录
     *
     * @param Rest $rest
     *
     * @return mixed
     * @throws \Exception
     * @throws \sdk\Base\Exception\BaseException
     * @throws \sdk\Rest\Exception\RestException
     */
    protected function restGetMulti(Rest $rest)
    {
        if ( ! $this->_enableGetMulti)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'GET(multi)']));
        }

        try
        {
            $this->_restGetMultiModel = $this->prepareModel();
            $result = [];

            // 设置查询条件
            $this->_restGetMultiModel = $this->_restGetMultiModel->values((array) $rest->query());

            // 是否返回所有记录
            // 如果允许返回所有记录，并且又有提交参数的话，下面的limit就会失效
            $getAll = false;
            if ($this->_restGetMultiAll && $rest->query('_all'))
            {
                $getAll = true;
            }

            $hasLimit = false;
            // 额外的查询条件
            foreach ((array) $rest->query() as $k => $v)
            {
                // 以 _ 开头的变量为额外的查询条件，如_limit、_offset
                if ($k{0} == '_')
                {
                    switch ($k)
                    {
                        case '_search':
                            $this->_restGetMultiModel->whereOpen();
                            foreach ($this->_restGetMultiModel->tableColumns() as $column => $columnConfig)
                            {
                                if (is_numeric($v) && isset($columnConfig['type']) && in_array($columnConfig['type'], ['int']))
                                {
                                    $this->_restGetMultiModel->orWhere($column, '=', $v);
                                }
                                else
                                {
                                    $this->_restGetMultiModel->orWhere($column, 'LIKE', "%$v%");
                                }
                            }
                            $this->_restGetMultiModel->whereClose();
                            break;
                        case '_limit':
                            if ( ! $getAll)
                            {
                                $this->_restGetMultiModel->limit($v);
                                $hasLimit = true;
                            }
                            break;
                        case '_offset':
                            $this->_restGetMultiModel->offset($v);
                            break;
                        case '_groupBy':
                            $this->_restGetMultiModel->groupBy($v);
                            break;
                        case '_sort':
                            if (is_string($v))
                            {
                                $v = json_decode($v, true);
                            }
                            foreach ((array) $v as $sortKey => $sortDirection)
                            {
                                $this->_restGetMultiModel->orderBy($sortKey, strtolower($sortDirection));
                            }
                            break;
                        default:
                            // ignore
                    }
                }
            }

            // 如果没有传limit参数，那么就强制使用默认的limit参数
            if ( ! $hasLimit && ! $getAll && $rest->param('id') != '_count')
            {
                $this->_restGetMultiModel->limit($this->_restGetMultiLimit);
            }

            // 计算总数
            if ($rest->param('id') == '_count')
            {
                $this->handleModelCall($this->_restGetMultiModel);
                $count = count($this->_restGetMultiModel->findAll());
                $result = ['count' => $count];
            }
            else
            {
                $this->handleModelCall($this->_restGetMultiModel);

                if ($this->_restGetMultiModel instanceof RestDbModel)
                {
                    //$this->_restGetMultiModel->asObject('sdk\Mvc\Model\Model');
                    $models = $this->_restGetMultiModel->findAll();
                }
                else
                {
                    $models = $this->_restGetMultiModel->findAll();
                }

                foreach ($models as $model)
                {
                    if ($model instanceof stdClass)
                    {
                        $result[] = (array) $model;
                    }
                    elseif ($model instanceof Model)
                    {
                        $result[] = $model->asArray();
                    }
                    else
                    {
                        $result[] = $model;
                    }
                }
            }
        }
        catch (BaseException $e)
        {
            //throw new RestException($e->getMessage(), [], 500);
            throw $e;
        }
        return $result;
    }

    /**
     * @var  Model
     */
    protected $_restPutModel;

    /**
     * 一般PUT为更新操作
     *
     * @param Rest $rest
     *
     * @return array
     */
    public function restPut(Rest $rest)
    {
        if ( ! $this->_enablePut)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'PUT']));
        }

        $this->_restPutModel = $this->prepareModel();
        $this->_restPutModel->where($this->_restPutModel->primaryKey(), '=', $rest->param('id'));
        $this->handleModelCall($this->_restPutModel);
        $this->_restPutModel->find();
        if ( ! $this->_restPutModel->loaded())
        {
            $rest->sendCode(404, __('Resource not found, ID: :id', [
                ':id' => $rest->param('id')
            ]));
        }

        // PUT过来的数据在request的body中
        $putData = json_decode($rest->request->body, true);

        // 更新
        try
        {
            $this->_restPutModel
                ->values($putData, array_keys($this->_restPutModel->tableColumns()))
                ->save();
        }
        catch (ValidationException $e)
        {
            $rest->sendCode(403, $e->errors('rest')); // 更新出错
        }
        $rest->sendCode(200, $this->_restPutModel->asArray());
    }

    /**
     * @var  Model
     */
    protected $_restPostModel;

    /**
     * 一般POST为新建操作
     *
     * @param Rest $rest
     */
    public function restPost(Rest $rest)
    {
        if ( ! $this->_enablePost)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'POST']));
        }

        $postData = json_decode(file_get_contents('php://input'), true);

        $this->_restPostModel = $this->prepareModel();
        $this->handleModelCall($this->_restPostModel);
        try
        {
            $this->_restPostModel
                ->values($postData, array_keys($this->_restPostModel->tableColumns()))
                ->save();
        }
        catch (ValidationException $e)
        {
            $rest->sendCode(403, $e->errors('rest')); // 创建出错
        }
        $rest->sendCode(200, $this->_restPostModel->asArray()); // 创建出错
    }

    /**
     * @var  Model
     */
    protected $_restDeleteModel;

    /**
     * 删除记录
     *
     * @param Rest $rest
     */
    public function restDelete(Rest $rest)
    {
        if ( ! $this->_enableDelete)
        {
            $rest->sendCode(405, __(':method method is disabled.', [':method' => 'DELETE']));
        }

        $this->_restDeleteModel = $this->prepareModel();
        $this->_restDeleteModel->where($this->_restDeleteModel->primaryKey(), '=', $rest->param('id'));
        $this->handleModelCall($this->_restDeleteModel);
        $this->_restDeleteModel->find();
        if ( ! $this->_restDeleteModel->loaded())
        {
            $rest->sendCode(404, __('Resource not found, ID: :id', [
                ':id' => $rest->param('id')
            ]));
        }

        $this->_restDeleteModel->delete();
        $rest->sendCode(204, __('Delete successfully.'));
    }

    /**** 下面是一些允许公开访问的方法 ****/

    /**
     * @return string 当前使用的模型名称
     */
    final public function getModelName()
    {
        return $this->_modelName;
    }

    final public function setModelName($modelName)
    {
        $this->_modelName = $modelName;
    }

    /**
     * @return  string  返回当前模型的描述信息
     */
    final public function getModelDesc()
    {
        return $this->_modelDesc;
    }

    /**
     * 设置模型描述
     *
     * @param $modelDesc
     *
     * @return string
     */
    final public function setModelDesc($modelDesc)
    {
        $this->_modelDesc = $modelDesc;
    }

    /**
     * 是否允许GET方法
     */
    final public function allowGet()
    {
        return $this->_enableGet ? 1 : 0;
    }

    /**
     * 是否允许POST方法
     */
    final public function allowPost()
    {
        return $this->_enablePost ? 1 : 0;
    }

    /**
     * 是否允许PUT方法
     */
    final public function allowPut()
    {
        return $this->_enablePut ? 1 : 0;
    }

    /**
     * 是否允许DELETE方法
     */
    final public function allowDelete()
    {
        return $this->_enableDelete ? 1 : 0;
    }

    /**
     * 是否允许OPTIONS方法
     */
    final public function allowOptions()
    {
        return $this->_enableOptions ? 1 : 0;
    }

    /**
     * 是否允许HEAD方法
     */
    final public function allowHead()
    {
        return $this->_enableHead ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getModelTitle()
    {
        return $this->_modelTitle;
    }

    /**
     * @param string $modelTitle
     */
    public function setModelTitle($modelTitle)
    {
        $this->_modelTitle = $modelTitle;
    }
}
