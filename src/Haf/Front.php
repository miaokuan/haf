<?php
/**
 * high available framework front
 * @author miaoxiukuan
 */

namespace Haf;

use Haf\Request;
use Haf\Action;
use Haf\Exception;

class Front
{
	protected $request;

	protected static $instance = null;

	protected $dispatched = false;

	/**
	 * @return Front|null
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

		while($this->getDispatched() === false) {
			$this->setDispatched(true);
			$controller = $this->getController();
			try {
				$obj = new $controller($this->request);
				if (!($obj instanceof Action)) {
                    $message = "Controller: $controller is not an instance of Haf/Action.";
					throw new Exception($message, Exception::ERRNO_INSTANCE_NOT_EXTENDS_ACTION);
				}
				$action = $this->getAction();
				try{
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

	/**
	 * @return string
	 */
	public function getController()
	{
		$controller = $this->request->getController();
		return 'Controller\\' . $controller;
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		$action = $this->request->getAction();
		return $action.'Action';
	}

	/**
	 * @param $dispatched
	 * @return $this
	 */
	public function setDispatched($dispatched)
	{
		$this->dispatched = $dispatched ? true : false;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getDispatched()
	{
		return $this->dispatched;
	}

}
