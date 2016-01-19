<?php
/**
 * high available framework front
 * @author miaoxiukuan
 */

namespace Haf;

class Front
{
    protected $request;

    protected static $instance = null;

    protected $namespace = 'Controller\\';

    /**
     * @return Front|null
     */
    public static function singleton()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    protected function __construct()
    {}

    protected function __clone()
    {}

    /**
     * @param \Haf\Request|null $request
     * @throws \Haf\Exception
     */
    public function dispatch(Request $request = null)
    {
        if (null === $request) {
            $this->request = Request::singleton();
        } else {
            $this->request = $request;
        }

        while ($this->request->getDispatched() === false) {
            $this->request->setDispatched(true);
            $controller = $this->getController();
            try {
                $obj = new $controller($this->request);
                if (!($obj instanceof Action)) {
                    $message = "Controller: $controller is not an instance of Haf/Action.";
                    throw new Exception($message, Exception::ERRNO_INSTANCE_NOT_EXTENDS_ACTION);
                }
                $action = $this->getAction();
                try {
                    $obj->dispatch($action);
                } catch (Exception $e) {
                    throw $e;
                }
                $obj = null;
            } catch (Exception $e) {
                throw $e;
            }
        }

    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        $controller = $this->request->getController();
        return $this->namespace . $controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        $action = $this->request->getAction();
        return $action;
    }

}
