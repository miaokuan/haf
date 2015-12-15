<?php
/**
 * high available framework action
 * @author miaoxiukuan
 */

namespace Haf;

use Haf\Request;
use Haf\Front;
use Haf\Exception;
use Haf\View;

abstract class Action
{

	protected $request = null;

	protected $view = null;

	protected $front = null;

	protected $methods = null;

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

	/**
	 * @return \Haf\View
	 */
	public function initView()
	{
		if (null === $this->view) {
			$this->view = new View();

			// @todo:
			$this->view->setPath(__home . '/tpl');
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
		
		$controller = str_replace('_', '/', $controller);
		$template = $controller . '/' . $template;
		
		return $template;
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
		} else {
			$this->__call($action, array());
		}
	}

    /**
     * @return \Haf\Front
     * @throws \Haf\Exception
     */
    public function front()
    {
        if (null !== $this->front) {
            return $this->front;
        }

        if (class_exists('Haf\\Front', false)) {
            $this->front = Front::singleton();
            return $this->front;
        }

        throw new Exception('Front controller class has not been loaded', Exception::ERRNO_FRONT_NOT_LOAD);
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

		$this->request->setAction($action);
		
		$this->front()->setDispatched(false);
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
