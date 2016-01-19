<?php
/**
 * high available framework view
 * @author miaoxiukuan
 */

namespace Haf {

    use Haf\Exception;

    class View
    {

        protected $file = null;

        protected $path;

        protected $data = array();

        public function __construct()
        {
            $GLOBALS['_haf_safe_view'] = $this;
        }

        /**
         * @param $template
         * @param bool|false $return
         * @return bool|string
         * @throws \Haf\Exception
         */
        public function render($template, $return = false)
        {
            $this->file = $this->script($template);

            ob_start();

            // avoid access $this params
            haf_safe_include_file($this->file, $this->data);

            $content = ob_get_clean();

            if ($return) {
                return $content;
            } else {
                echo $content;
            }
            return true;
        }

        /**
         * @param $name
         * @return string
         * @throws \Haf\Exception
         */
        protected function script($name)
        {
            if (is_readable($this->path . $name)) {
                return $this->path . $name;
            }

            $message = "script '$name' not found in path (" .
            $this->path . ")";
            throw new Exception($message, Exception::ERRNO_VIEW_SCRIPT);
        }

        /**
         * @param $spec
         * @param null $value
         * @return $this
         * @throws \Haf\Exception
         */
        public function assign($spec, $value = null)
        {
            if (is_string($spec)) {
                $this->data[$spec] = $value;
            } elseif (is_array($spec)) {
                foreach ($spec as $key => $val) {
                    $this->data[$key] = $val;
                }
            } else {
                $message = 'assign() expects a string or array, received ' .
                gettype($spec);
                throw new Exception($message, Exception::ERRNO_VIEW_ASSIGN);
            }

            return $this;
        }

        /**
         * @param $path
         */
        public function setPath($path)
        {
            $this->path = rtrim($path, '/') . '/';
        }

    }

/**
 * @param $file
 * @param $data
 */
    function haf_safe_include_file($file, array $data)
    {
        extract($data);
        include $file;
    }
}

namespace {
    function _inc($file)
    {
        $view = $GLOBALS['_haf_safe_view'];
        $view->render($file);
    }
}
