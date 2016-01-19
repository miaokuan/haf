<?php
/**
 * high available framework action
 * @author miaoxiukuan
 */

namespace Haf;

abstract class Action
{

    protected $request = null;

    protected $view = null;

    protected $methods = null;

    protected $enable_view = true;

    /**
     * @param \Haf\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
    }

    public function init()
    {}

    public function disableView()
    {
        $this->enable_view = false;
    }

    public function enableView()
    {
        $this->enable_view = true;
    }

    /**
     * @return \Haf\View
     */
    public function initView()
    {
        if (null === $this->view) {
            $this->view = new View();

            // @todo:
            $this->view->setPath(HOME_DIR . '/views');
        }

        return $this->view;
    }

    /**
     * @param $name
     * @param null $value
     * @return $this
     * @throws \Haf\Exception
     */
    public function assign($name, $value = null)
    {
        $view = $this->initView();
        $view->assign($name, $value);

        return $this;
    }

    /**
     * @param null $action
     * @param null $controller
     * @param bool|false $return
     * @return string
     * @throws \Haf\Exception
     */
    public function render($action = null, $controller = null, $return = false)
    {
        $view = $this->initView();
        $template = $this->template($action, $controller);
        return $view->render($template, $return);
    }

    /**
     * @param null $action
     * @param null $controller
     * @param string $ext
     * @return string
     * @throws \Haf\Exception
     */
    public function template($action = null, $controller = null, $ext = '.phtml')
    {
        if (null === $action) {
            $action = $this->request->getAction();
        } elseif (!is_string($action)) {
            throw new Exception('Invalid action specifier for view render', Exception::ERRNO_ACTION_NOT_STRING);
        }

        $template = $action . $ext;

        if (!$controller) {
            $controller = $this->request->getController();
        }

        $controller = str_replace('\\', '/', $controller);
        $template = $controller . '/' . $template;

        return strtolower($template);
    }

    /**
     * @param $template
     * @param bool|false $return
     * @return string
     */
    public function renderTemplate($template, $return = false)
    {
        $view = $this->initView();
        return $view->render($template, $return);
    }

    /**
     * @param $action
     * @throws \Haf\Exception
     */
    public function dispatch($action)
    {
        if (null === $this->methods) {
            $this->methods = get_class_methods($this);
        }

        if (in_array($action, $this->methods)) {
            $this->$action();

            if ($this->enable_view && $this->request->getDispatched()) {
                $this->render();
            }
        } else {
            $this->__call($action, array());
        }
    }

    /**
     * @param $action
     * @param null $controller
     * @param array|null $params
     * @throws \Haf\Exception
     */
    final protected function forward($action, $controller = null, array $params = null)
    {
        if (null !== $params) {
            $this->request->setParams($params);
        }

        if (null !== $controller) {
            $this->request->setController($controller);
        }

        $this->request->setAction($action)->setDispatched(false);
    }

    /**
     * @param $method
     * @param $args
     * @throws \Haf\Exception
     */
    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            $action = substr($method, 0, strlen($method) - 6);
            throw new Exception(sprintf('Action "%s" does not exist and was not trapped in __call()', $action), Exception::ERRNO_ACTION_NOT_EXIST);
        }
        throw new Exception(sprintf('Method "%s" does not exist and was not trapped in __call()', $method), Exception::ERRNO_METHOD_NOT_EXIST);
    }

}
