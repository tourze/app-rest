<?php

namespace sdk\Rest;

use sdk\Base\Log;
use sdk\Base\Object;
use sdk\Base\Helper\Text;
use sdk\Base\Helper\Arr;
use sdk\Base\I18n;
use sdk\Base\Sdk;
use sdk\Http\HttpResponse;
use sdk\Http\HttpRequest;
use sdk\Rest;
use sdk\Rest\Exception\RestException;
use SimpleXMLElement;

/**
 * REST核心处理类
 *
 * @property  HttpRequest       request
 * @property  HttpResponse      response
 * @property  string            method
 * @property  RestModel         model
 * @property  string            controller
 * @property  mixed             result
 * @property  string            contentType
 * @property  string            token
 * @property  array             user
 * @property  string            dbInstance
 */
abstract class RestCore extends Object
{

    /**
     * @var string 使用的数据库实例名称
     */
    protected $_dbInstance = null;

    /**
     * @var array 支持的方法
     */
    public static $_methods = [
        'GET',
        'PUT',
        'POST',
        'DELETE',
        'HEAD',
        'TRACE',
        'PATCH',
        'OPTIONS'
    ];

    /**
     * @var array 后缀和对应的mime
     */
    public static $_types = [
        'text/html'                => 'html',
        'application/json'         => 'json',
        'application/xml'          => 'xml',
        'application/rdf+xml'      => 'rdf',
        'application/rss+xml'      => 'rss',
        'application/atom+xml'     => 'atom',
        'application/vnd.ms-excel' => 'csv',
        'application/excel'        => 'xls',
    ];

    /**
     * @var array CORS设置
     */
    public static $_cors = [
        'origin'      => '*',
        'methods'     => null,
        'headers'     => [
            'Origin',
            'Accept',
            'Accept-Language',
            'Content-Type',
            'X-Requested-With',
            'X-CSRF-Token'
        ],
        'expose'      => null,
        'credentials' => null,
        'age'         => null
    ];

    /**
     * @var string 命名空间前缀
     */
    public static $restImplementer = '\\rest\\';

    /**
     * 格式化表名
     *
     * @param      $tableName
     * @param null $user
     *
     * @return mixed|string
     */
    public static function formatTableName($tableName, $user = null)
    {
        if ($user !== null)
        {
            $tableName = $user . '_' . $tableName;
        }
        $tableName = str_replace('/', '_', $tableName);
        return $tableName;
    }

    /**
     * rest核心初始化
     *
     * @param mixed $args
     */
    public function __construct($args = [])
    {
        /** @var HttpRequest $request */
        $request = Arr::get($args, 'request', HttpRequest::initial());
        $this->request = $request;

        $this->controller = Arr::get($args, 'controller');
        $this->response = Arr::get($args, 'response', new HttpResponse());
        $this->method = Arr::get($args, 'method', $request->method);
        $this->model = Arr::get($args, 'model', $request->action);
        $this->contentType = Arr::get($args, 'contentTypes', $this->accept());
        $this->charset = Arr::get($args, 'charsets', [Sdk::$charset]);
        $this->language = Arr::get($args, 'languages', [I18n::$lang]);
        $this->dbInstance = Arr::get($args, 'dbInstance', null);

        unset($args['controller'], $args['request'], $args['response'], $args['method'], $args['model']);
        $this->_config = $args;
    }

    /**
     * @var  array  配置数据
     */
    protected $_config;

    /**
     * @var  RestController  要执行的控制器
     */
    protected $_controller;

    /**
     * @var  RestModel  要执行的模型
     */
    protected $_model;

    /**
     * @var  HttpRequest  加载的Request实例
     */
    protected $_request;

    /**
     * @var  HttpResponse  Response实例
     */
    protected $_response;

    /**
     * @var  string  HTTP方法
     */
    protected $_method;

    /**
     * @var  string  content HTTP Accept type
     */
    protected $_contentType;

    /**
     * @var  string  当前使用的token
     */
    protected $_token;

    /**
     * @var  string  通过token识别到的当前用户的信息
     */
    protected $_user = [];

    /**
     * @var  String  charset HTTP Accept charset
     */
    protected $_charset;

    /**
     * @var  String  language HTTP Accept language
     */
    protected $_language;

    /**
     * @var  Mixed  result from the model's method
     */
    protected $_result;

    /**
     * Execute the REST model and save the results
     *
     * @param   void
     *
     * @return  mixed
     */
    public function execute()
    {
        // 检查HTTP方法是否允许访问
        $method = '\sdk\Rest\Method\\' . ucfirst($this->method) . 'Method';
        if ( ! $this->model instanceof $method)
        {
            $this->_response->headers('Allow', $this->allowed());
            $this->sendCode(405, __('Method :method not allowed.', [
                ':method' => $this->method
            ]));
        }

        // Check if this is a Cross-Origin Resource Sharing Model
        if ($this->model instanceof RestCors)
        {
            $this->model->restCors($this);
        }

        // 检查权限
        if ($this->model instanceof RestAuth)
        {
            if (false === $this->model->restAuth($this))
            {
                $this->sendCode(401, 'No Authentication.');
            }
        }

        $exec = 'rest' . ucfirst(strtolower($this->method));

        $result = false;
        // 只读方法的情况下，可以对结果进行缓存
        if (in_array($this->method, ['GET', 'OPTIONS']))
        {
            // 尝试读取缓存结果
            $cacheKey = 'rest.' . md5($_SERVER['REQUEST_URI']);
            $result = Sdk::cache($cacheKey);
        }

        if ( ! $result)
        {
            try
            {
                $result = $this->model->$exec($this);
            }
            catch (RestException $e)
            {
                $this->sendCode(404, __('Resource ":resource" not found.', [':resource' => $this->request->uri]));
            }
        }

        if (isset($cacheKey))
        {
            Sdk::cache($cacheKey, $result, 60 * 5);
        }

        $this->result = $result;
        $this->request->action = Rest::$_types[$this->contentType];

        Log::info($this->formatRestLog([
            'method' => $this->method,
            'uri'    => $this->request->uri,
            'code'   => $this->response->status,
        ]));

        $type = $this->contentType . '; charset=' . $this->_charset;
        $this->response->headers('Content-Type', $type);
        $this->response->headers('Content-Language', $this->_language);

        return $this;
    }

    /**
     * 格式化日志信息
     *
     * @param  array $params
     * @return string
     */
    public function formatRestLog($params)
    {
        $values = [];
        foreach ($params as $key => $value)
        {
            $values[] = "$key=$value";
        }
        return implode(', ', $values);
    }

    /**
     * Checks ETag, sends 304 on match, generates ETag header
     *
     * @param string $hash The hash used to generate the ETag, defaults to sha1
     *
     * @return RestCore
     */
    public function etag($hash = 'sha1')
    {
        $match = $this->request->headers('If-None-Match');
        $etag = $hash(json_encode($this->result));
        if ($match === $etag)
        {
            //$this->sendCode(304);
        }
        else
        {
            //$this->response->headers('ETag', $etag);
        }

        return $this;
    }

    /**
     * Returns the accepted content types based on Controller's interfaces
     *
     * @return mixed
     */
    public function accept()
    {
        return array_keys(self::$_types);
    }

    /**
     * Return the allowed methods of the model
     *
     * @param   void
     *
     * @return  mixed
     */
    public function allowed()
    {
        $allowed = [];
        foreach (Rest::$_methods as $method)
        {
            $class = '\sdk\Rest\Method\\' . ucfirst($method) . 'Method';
            if ($this->_model instanceof $class)
            {
                $allowed[] = $method;
            }
        }

        return $allowed;
    }

    /**
     * Get the short-name content type of the request
     *
     * @return string
     */
    public function type()
    {
        $type = $this->request->headers('Content-Type');

        return Arr::get(Rest::$_types, $type, $type);
    }

    /**
     * Retrieves a value from the route parameters.
     *     $id = $request->param('id');
     *
     * @param   string $key     Key of the value
     * @param   mixed  $default Default value if the key is not set
     *
     * @return  mixed
     */
    public function param($key = null, $default = null)
    {
        return $this->request->param($key, $default);
    }

    /**
     * Gets HTTP POST parameters to the request.
     *
     * @param   mixed $key Key or key value pairs to set
     *
     * @return  mixed
     */
    public function post($key = null)
    {
        return $this->request->post($key);
    }

    /**
     * Gets HTTP query string.
     *
     * @param   mixed $key Key or key value pairs to set
     * @param bool    $array
     *
     * @return mixed
     */
    public function query($key = null, $array = true)
    {
        if (is_null($key))
        {
            $query = $this->request->query($key);
            foreach ($query as $name => $value)
            {
                if ($value == '')
                {
                    return json_decode($name, $array);
                }
            }

            return $query;
        }

        return $this->request->query($key);
    }

    /**
     * Gets HTTP body to the request or response. The body is
     * included after the header, separated by a single empty new line.
     *
     * @param null      $type
     * @param   boolean $array Return an associative array, json only
     *
     * @return mixed
     * @internal param string $content Content to set to the object
     */
    public function body($type = null, $array = false)
    {
        $body = $this->request->body;
        $type = ($type) ? $type : $this->type();
        switch ($type)
        {
            case 'json':
                return json_decode($body, $array);
                break;
            case 'xml':
                return new SimpleXMLElement($body);
                break;
            default:
                return $body;
                break;
        }
    }

    /**
     * @return string
     */
    public function getDbInstance()
    {
        return $this->_dbInstance;
    }

    /**
     * @param string $dbInstance
     */
    public function setDbInstance($dbInstance)
    {
        $this->_dbInstance = $dbInstance;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }

    protected function getContentType()
    {
        return $this->_contentType;
    }

    protected function setContentType($contentType)
    {
        if (is_string($contentType))
        {
            $this->_contentType = $contentType;
        }
        if (is_array($contentType))
        {
            $this->_contentType = $this->request
                ->headers()
                ->preferredAccept($contentType);
        }

        if (false === $this->_contentType)
        {
            $this->sendCode(406, __('Supplied Accept types: :accept not supported. Supported types: :types', [
                ':accept' => $this->request->headers('Accept'),
                ':types'  => implode(', ', $contentType)
            ]));
        }
    }

    protected function getResult()
    {
        return $this->_result;
    }

    protected function setResult($result)
    {
        $this->_result = $result;
    }

    /**
     * 读取当前使用的request实例
     *
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * 设置当前使用的请求对象
     *
     * @param $request
     */
    public function setRequest($request)
    {
        $this->_request = $request;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    protected function getMethod()
    {
        return $this->_method;
    }

    protected function setMethod($method)
    {
        $this->_method = $method;
    }

    public function getModel()
    {
        return $this->_model;
    }

    /**
     * 设置当前模型
     *
     * @param $model
     *
     * @throws RestException
     */
    public function setModel($model)
    {
        if (is_object($model))
        {
            $this->_model = $model;
        }
        else
        {
            $class = self::$restImplementer . ucfirst(Text::camelize($model, '-'));
            // 已经有class文件了，直接加载
            if (class_exists($class))
            {
                $newModel = new $class;
            }
            else
            {
                $newModel = new RestDb([
                    'sourceTable' => $model
                ]);
            }

            if ($newModel->valid())
            {
                $this->sendCode(404, __('Resource ":model" not found.', [
                    ':model' => $model
                ]));
            }

            $this->_model = $newModel;
        }

        if ( ! $this->_model instanceof RestModel)
        {
            // Send the Internal Server Error response
            $this->sendCode(500, __('Class :class does not implement RestModel.', [
                ':class' => get_class($this->_model)
            ]));
        }
    }

    protected function getController()
    {
        return $this->_controller;
    }

    protected function setController($controller)
    {
        $this->_controller = $controller;
    }

    /**
     * 当前使用的字符集
     *
     * @return String
     */
    protected function getCharset()
    {
        return $this->_charset;
    }

    protected function setCharset($charset)
    {
        $this->_charset = $this->request->headers()->preferredCharset($charset);

        if (false === $this->_charset)
        {
            $this->sendCode(406, __('Supplied Accept-Charset: :accept not supported. Supported types: :types', [
                ':accept' => $this->request->headers('Accept-Charset'),
                ':types'  => implode(', ', $charset)
            ]));
        }
    }

    protected function getLanguage()
    {
        return $this->_language;
    }

    protected function setLanguage($language)
    {
        $this->_language = $this->request->headers()
            ->preferredLanguage($language);

        if (false === $this->_language)
        {
            $this->sendCode(406, __('Supplied Accept-Language: :accept not supported. Supported languages: :types', [
                ':accept' => $this->request->headers('Accept-Language'),
                ':types'  => implode(', ', $language)
            ]));
        }
    }

    /**
     * Allows setting the method from the X-HTTP-METHOD-OVERRIDE header
     *
     * @param boolean $override
     *
     * @return  Rest
     */
    public function methodOverride($override = false)
    {
        $method = $this->request->headers('X-HTTP-METHOD-OVERRIDE');
        $method = (isset($method) && $override) ? $method : $this->request->method;
        $this->method = $method;

        return $this;
    }

    /**
     * Allows setting the content type from the Request param ext
     *
     * @param boolean $override
     *
     * @return  Rest
     */
    public function contentOverride($override = false)
    {
        $types = $this->accept();

        if (false === $override)
        {
            $this->contentType = Arr::get($this->_config, 'contentTypes', $types);

            return $this;
        }

        $content = $this->request->param('ext', 'json');

        $key = array_search($content, self::$_types);
        if (false === $key)
        {
            $this->sendCode(406, __('Supplied Override Type: :accept not supported. Supported types: :types', [
                ':accept' => $content,
                ':types'  => implode(', ', $types)
            ]));
        }

        if ( ! in_array($key, $types))
        {
            $this->sendCode(406, __('Supplied Content Type: :accept not supported. Supported types: :types', [
                ':accept' => $key,
                ':types'  => implode(', ', $types)
            ]));
        }

        $this->contentType = $key;

        return $this;
    }

    protected $corsHeaders = [
        'origin'      => 'Access-Control-Allow-Origin',
        'methods'     => 'Access-Control-Allow-Methods',
        'headers'     => 'Access-Control-Allow-Headers',
        'expose'      => 'Access-Control-Expose-Headers',
        'credentials' => 'Access-Control-Allow-Credentials',
        'age'         => 'Access-Control-Max-Age',
    ];

    /**
     * Cross-Origin Resource Sharing Helper
     *
     * @param array $values
     *
     * @return Rest
     */
    public function cors(Array $values = [])
    {
        $cors = self::$_cors;
        $cors['methods'] = isset($values['methods']) ? $values['methods'] : $this->allowed();
        $cors = Arr::merge($cors, $values);

        foreach ($this->corsHeaders as $key => $header)
        {
            if (isset($cors[$key]))
            {
                $this->response->headers($header, is_array($cors[$key]) ? implode(', ', $cors[$key]) : $cors[$key]);
            }
        }

        return $this;
    }

    /**
     * Sends the response code and exits the application
     *
     * @param   int   $code
     * @param   mixed $body
     *
     * @throws  RestException
     */
    public function sendCode($code = 204, $body = null)
    {
        // 是否抛出异常
        if (false === Arr::get($this->_config, 'exceptions', false))
        {
            $body = [
                'code' => $code,
                'data' => $body,
            ];
            $body = json_encode($body);

            // 默认返回都是200，真实的状态码在body中
            $this->response->status = 200;
            $this->response->headers('Content-Type', 'application/json');
            $this->response->sendHeaders();
            $this->response->body = $body;
            echo $this->response;

            if ($code == 404)
            {
                Log::warning($this->formatRestLog([
                    'method' => $this->method,
                    'uri'    => $this->request->uri,
                    'code'   => $code,
                ]));
            }
            elseif ($code >= 500)
            {
                Log::error($this->formatRestLog([
                    'method' => $this->method,
                    'uri'    => $this->request->uri,
                    'code'   => $code,
                ]));
            }
            else
            {
                Log::info($this->formatRestLog([
                    'method' => $this->method,
                    'uri'    => $this->request->uri,
                    'code'   => $code,
                ]));
            }

            exit;
        }
        else
        {
            // See if special exception class exists
            $class = 'sdk\Http\Exception\Http'.$code.'Exception';
            if (class_exists($class))
            {
                if (is_array($body))
                {
                    list($str, $pairs) = $body;
                    throw new $class($str, $pairs);
                }
                else
                {
                    throw new $class($body);
                }
            }
            else
            {
                if (is_array($body))
                {
                    list($str, $pairs) = $body;
                    throw new RestException($str, $pairs, $code);
                }
                else
                {
                    throw new RestException($body, null, $code);
                }
            }
        }
    }
}
