<?php
/**
 * high available framework request
 * @author miaoxiukuan
 */

namespace Haf;

class Request
{
    protected static $instance = null;

    protected $controller;

    protected $action;

    protected $params = array();

    protected $server = array();

    /**
     * @return Request|null
     */
    public static function singleton()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct()
    {}

    protected function __clone()
    {}

    /**
     * @param string $str
     * @return string
     */
    public function formatController($str)
    {
        $arr = explode('.', strtolower($str));
        foreach ($arr as $key => $val) {
            $arr[$key] = ucfirst($val);
        }

        return implode('_', $arr);
    }

    /**
     * @return string
     */
    public function getController()
    {
        if (null === $this->controller) {
            $c = $this->get('c');
            if (empty($c)) {
                $c = 'index';
            }
            $this->controller = $this->formatController($c);
        }
        return $this->controller;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setController($name)
    {
        $this->controller = $this->formatController($name);
        return $this;
    }

    /**
     * @param $str
     * @return string
     */
    public function formatAction($str)
    {
        $arr = explode('-', strtolower($str));
        foreach ($arr as $key => $val) {
            if ($key > 0) {
                $val = ucfirst($val);
            }
            $arr[$key] = $val;
        }
        return implode('', $arr);
    }

    /**
     * @return string
     */
    public function getAction()
    {
        if (null === $this->action) {
            $ac = $this->get('ac');
            if (empty($ac)) {
                $ac = 'index';
            }
            $this->action = $this->formatAction($ac);
        }
        return $this->action;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setAction($name)
    {
        $this->action = $this->formatAction($name);
        return $this;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        switch (true) {
            case isset($this->params[$key]):
                return $this->params[$key];
            case isset($_GET[$key]):
                return $_GET[$key];
            case isset($_POST[$key]):
                return $_POST[$key];
            default:
                return null;
        }
    }

    /**
     * @param $key
     * @param null $callback
     * @return mixed|null
     */
    public function get($key, $callback = null)
    {
        if ($callback === null) {
            return $this->__get($key);
        }
        return call_user_func_array($callback, $this->__get($key));
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        switch (true) {
            case isset($this->params[$key]):
                return true;
            case isset($_GET[$key]):
                return true;
            case isset($_POST[$key]):
                return true;
            default:
                return false;
        }
    }

    /**
     * @param $params
     * @return array
     */
    public function from($params)
    {
        $args = is_array($params) ? $params : func_get_args();

        $result = array();
        foreach ($args as $arg) {
            $result[$arg] = $this->__get($arg);
        }
        return $result;
    }

    /**
     * @param string|null $key
     * @return mixed|null
     */
    public function getQuery($key = null)
    {
        if (null === $key) {
            return $_GET;
        }
        return array_key_exists($key, $_GET) ? $_GET[$key] : null;
    }

    /**
     * @param null $key
     * @return null
     */
    public function getPost($key = null)
    {
        if (null === $key) {
            return $_POST;
        }

        return (array_key_exists($key, $_POST)) ? $_POST[$key] : null;
    }

    /**
     * @param null $key
     * @return null
     */
    public function getCookie($key = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (array_key_exists($key, $_COOKIE)) ? $_COOKIE[$key] : null;
    }

    /**
     * @param $key
     * @return null
     */
    public function getParam($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $key = (string) $key;
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetParam($key)
    {
        if (array_key_exists($key, $this->params)) {
            unset($this->params[$key]);
        }
        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function issetParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * @param $params
     * @return $this
     */
    public function setParams($params)
    {
        if (!is_array($params)) {
            parse_str($params, $out);
            $params = $out;
        }
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearParams()
    {
        $this->params = array();
        return $this;
    }

    /**
     * @param $key
     * @param null $value
     */
    public function setServer($key, $value = null)
    {
        if (null === $value) {
            if (isset($_SERVER[$key])) {
                $value = $_SERVER[$key];
            } elseif (isset($_ENV[$key])) {
                $value = $_ENV[$key];
            }
        }

        $this->server[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getServer($key)
    {
        if (!isset($this->server[$key])) {
            $this->setServer($key);
        }

        return $this->server[$key];
    }

    /**
     * @param bool|true $checkProxy
     * @return mixed|string
     */
    public function getIp($checkProxy = true)
    {
        if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }

        if ($pos = strpos($ip, ',')) {
            $ip = substr($ip, 0, $pos);
        }
        return $ip;
    }

    /**
     * @return mixed
     */
    public function getAgent()
    {
        return $this->getServer('HTTP_USER_AGENT');
    }

    /**
     * @return mixed
     */
    public function getRefer()
    {
        return $this->getServer('HTTP_REFERER');
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return 'GET' == $this->getServer('REQUEST_METHOD');
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return 'POST' == $this->getServer('REQUEST_METHOD');
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return 'PUT' == $this->getServer('REQUEST_METHOD');
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return 'DELETE' == $this->getServer('REQUEST_METHOD');
    }

    /**
     * @return bool
     */
    public function isHttps()
    {
        return 'on' == $this->getServer('HTTPS');
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return 'XMLHttpRequest' == $this->getServer('HTTP_X_REQUESTED_WITH');
    }

    /**
     * @return bool
     */
    public function isFlash()
    {
        return preg_match('/flash/i', $this->getServer('USER_AGENT')) ? true : false;
    }

}
