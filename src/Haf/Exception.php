<?php
/**
 * high available framework exception
 * @author miaoxiukuan
 */

namespace Haf;

class Exception extends \Exception
{
    const ERRNO = 560;

    const ERRNO_ACTION_NOT_STRING = 561;

    const ERRNO_ACTION_NOT_EXIST = 562;

    const ERRNO_METHOD_NOT_EXIST = 563;

    const ERRNO_FRONT_NOT_LOAD = 564;

    const ERRNO_INSTANCE_NOT_EXTENDS_ACTION = 565;

    const ERRNO_VIEW_SCRIPT = 566;

    const ERRNO_VIEW_ASSIGN = 567;

    /**
     * @param string $message
     * @param integer $code
     */
    public function __construct($message, $code = self::ERRNO)
    {
        parent::__construct($message, $code);
    }

}
