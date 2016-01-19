<?php
/**
 * high available framework response
 * @author miaoxiukuan
 */

namespace Haf;

class Response
{
    /**
     * http code description
     * @var array
     */
    protected static $httpCode = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * @param $filename
     * @param string $ua
     * @param int $filesize
     */
    public static function download($filename, $ua = '', $filesize = 0)
    {
        $encoded_filename = rawurlencode($filename);
        if ('' == $ua) {
            $ua = $_SERVER["HTTP_USER_AGENT"];
        }

        // fix for IE catching or PHP bug issue
        header("Pragma: public");
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        // browser must download file from server instead of cache

        // force download dialog
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        /*
        The Content-transfer-encoding header should be binary, since the file will be read
        directly from the disk and the raw bytes passed to the downloading computer.
        The Content-length header is useful to set for downloads. The browser will be able to
        show a progress meter as a file downloads. The content-lenght can be determines by
        filesize function returns the size of a file.
         */
        header("Content-Transfer-Encoding: binary");

        if ($filesize > 0) {
            header("Content-Length: $filesize");
        }

        ob_clean();
        flush();
    }

    /**
     * @param $code
     */
    public static function setStatus($code)
    {
        if (isset(self::$httpCode[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . self::$httpCode[$code], true, $code);
        }
    }

    /**
     * 404 not found
     */
    public static function notFound()
    {
        self::setStatus(404);
        echo 'File Not Found.';
        exit;
    }

    /**
     * @param $location
     * @param bool|false $isPermanently
     */
    public static function redirect($location, $isPermanently = false)
    {
        if ($isPermanently) {
            self::setStatus(301);
            header('Location: ' . $location);
            echo '<html><head>
<title>301 Moved Permanently</title>
</head><body>
<h1>Moved Permanently</h1>
<p>The document has moved <a href="' . $location . '">here</a>.</p>
</body></html>';
        } else {
            self::setStatus(302);
            header('Location: ' . $location);
            echo '<html><head>
<title>302 Moved Temporarily</title>
</head><body>
<h1>Moved Temporarily</h1>
<p>The document has moved <a href="' . $location . '">here</a>.</p>
</body></html>';

        }
        exit;
    }

    /**
     * @param null $anchor
     * @param null $default
     */
    public static function back($anchor = null, $default = null)
    {
        $refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (!empty($refer)) {
            if (!empty($anchor)) {
                $parts = parse_url($refer);
                if (isset($parts['fragment'])) {
                    $refer = substr($refer, 0, strlen($refer) - strlen($parts['fragment']) - 1);
                }
            }
            self::redirect($refer . (empty($anchor) ? null : '#' . $anchor), false);
        } else if (!empty($default)) {
            self::redirect($default);
        }
    }

    /**
     * @param $message
     */
    public static function history($message)
    {
        $message = (string) $message;
        echo '<html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"/></head><body><script language="javascript">alert("' . $message . '");history.go(-1);</script></body></html>';
        exit;
    }

}
