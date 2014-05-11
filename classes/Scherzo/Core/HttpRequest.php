<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link       http://github.com/MrAnchovy/scherzo
 * @copyright  Copyright © 2014 [MrAnchovy](http://www.mranchovy.com/)
 * @license    MIT
**/

namespace Scherzo\Core;

use Exception, Scherzo\Core\ScherzoException;

/**
 * Handle an HTTP request.
**/
class HttpRequest extends \Scherzo\Core\Service
{
    /**
     * Request information parsed from headers
    **/
    public $scriptName;
    public $protocol;
    public $method;
    public $https;
    public $referer;
    public $userAgent;
    public $requestedWith;
    public $clientIp;
    public $body;
    public $proxyIp;
    public $untrustedIp;
    public $host;
    public $path;
    public $scheme;

    /**
     * Request parameters.
    **/
    protected $params;

    protected $trustedProxies = array();

    public function afterConstructor()
    {
        $this->parse();
    }


    public function accepts($type = null) {
        $accepts = $this->getHeader('accept');
        if ($type === null) {
            return $accepts;
        } else {
            return (strpos($type, $accepts) !== false);
        }
    }

    public function parse() {
        $this->scriptName    = isset($_SERVER['SCRIPT_NAME'])           ? $_SERVER['SCRIPT_NAME'] : null;
        $this->host          = isset($_SERVER['HTTP_HOST'])             ? $_SERVER['HTTP_HOST'] : null;
        // $this->path          = isset($_SERVER['REQUEST_URI'])           ? ltrim($this->getServer('REQUEST_URI'), '/') : null;
        $this->path          = isset($_SERVER['REQUEST_URI'])           ? $_SERVER['REQUEST_URI'] : '/';
        $this->protocol      = isset($_SERVER['SERVER_PROTOCOL'])       ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $this->method        = isset($_SERVER['REQUEST_METHOD'])        ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $this->referer       = isset($_SERVER['HTTP_REFERER'])          ? $_SERVER['HTTP_REFERER'] : null;
        $this->userAgent     = isset($_SERVER['HTTP_USER_AGENT'])       ? $_SERVER['HTTP_USER_AGENT'] : null;
        $this->requestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
        $this->https         = !empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN);
        $this->scheme        = $this->https ? 'https' : 'http';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->parseProxy($_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $this->parseProxy($_SERVER['HTTP_CLIENT_IP']);
        } else {
            $this->clientIp = $_SERVER['REMOTE_ADDR'];
        }

    }
 
    // TODO consider treating it as a stream
    public function getBody()
    {
        return file_get_contents('php://input');
    }

    public function getCookie($name = null, $default = null)
    {
        if ($name === null) {
            return $_COOKIE;
        } else {
            return array_key_exists($name, $_COOKIE) ? $_COOKIE[$name] : $default;
        }
    }

    protected function parseProxy($forwardedFor) {
        if (in_array($_SERVER['REMOTE_ADDR'], $this->trustedProxies)) {
            $this->clientIp = array_shift(explode(',', $forwardedFor));
            $this->proxyIp = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->clientIp = $_SERVER['REMOTE_ADDR'];
            $this->untrustedIp = array_shift(explode(',', $forwardedFor));
        }
    }

    /**
    **/
    public function getHeader($name = null, $default = null)
    {
        $name = 'HTTP_' . str_replace('-', '_', strtoupper($name));
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        } else {
            return $default;
        }
    }

    public function getParam($name = null, $default = null)
    {
        if ($this->params === null) {
            $this->loadParams();
        }
        if ($name === null) {
            return $this->params;
        } else {
            return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
        }
    }

    public function getQuery($name = null, $default = null)
    {
        if ($name === null) {
            return $_GET;
        } elseif ($name === true) {
            return $_SERVER['QUERY_STRING'];
        } else {
            return array_key_exists($name, $_GET) ? $_GET[$name] : $default;
        }
    }

    protected function loadParams() {
        if ($this->getMethod() === 'GET') {
            $this->params = $_GET;
        } else {
            switch ($_SERVER['CONTENT_TYPE']) {
                case 'application/x-www-form-urlencoded':
                    $this->params = $_POST;
                    break;
                case 'application/json':
                    $this->params = json_decode($this->getBody());
                    break;
                default:
            }
        }
    }



/**

Request signature

$url = 'http://username:password@hostname/path?arg=value#anchor';

print_r(parse_url($url));

echo parse_url($url, PHP_URL_PATH);
?>

The above example will output:

Array
(
    [scheme] => http
    [host] => hostname
    [user] => username
    [pass] => password
    [path] => /path
    [query] => arg=value
    [fragment] => anchor
)

{url:"https://HTTP_HOST:IP/path?query#fragment",user}

    [HTTP_HOST] => localhost
    [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 6.3; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0
    [HTTP_ACCEPT_LANGUAGE] => en-gb,en;q=0.5
    [HTTP_ACCEPT_ENCODING] => gzip, deflate



    if ($_SERVER['HTTP_CLIENT_IP']) // Start capturing his ip
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN'; // If it can't catch it

if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARTDED_FOR'] != '') {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

Array
(
/**

    [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,* /*;q=0.8

    [SERVER_NAME] => localhost
    [SERVER_ADDR] => ::1
    [SERVER_PORT] => 8003
    [REMOTE_ADDR] => ::1
'HTTP_REFERER'
'HTTPS'
    [REQUEST_SCHEME] => http

    [REMOTE_PORT] => 58178

    [GATEWAY_INTERFACE] => CGI/1.1
    [SERVER_PROTOCOL] => HTTP/1.1
    [REQUEST_METHOD] => GET
    [QUERY_STRING] => 
    [REQUEST_URI] => /calc/paye/main
    [REQUEST_TIME_FLOAT] => 1396463876.012
    [REQUEST_TIME] => 1396463876

*/



    protected function deprecated() {
                    // get the base url
            $this->base = basename($_SERVER['SCRIPT_NAME']);

            // Rebuild requested uri scheme://server[:port]/[path][?query][#fraction]

            $this->scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';

            // get the host, including the port if any
            $this->host = $_SERVER['HTTP_HOST'];

            $this->hostName = $_SERVER['SERVER_NAME'];
            $this->hostPort = (int)$_SERVER['SERVER_PORT'];


            $this->url = "$this->scheme://$this->host/$this->path"
                 . empty($this->query) ? '' : "?$this->query"
                 . empty($this->fragment) ? '' : "#$this->fragment";

    }

    protected function parseJson()
    {
        $decoder = new JsonDecoder;
        $decoder->assoc = true; // we want an array
        $decoded = $decoder->decode($this->getBody());
        if (is_array($decoded)) {
            $this->params = $decoded;
        } else {
            $this->params = array();
        }
    }

} // end class HttpRequest
