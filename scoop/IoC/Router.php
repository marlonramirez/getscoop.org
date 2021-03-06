<?php
namespace Scoop\IoC;

class Router
{
    private $routes = array();
    private $current;

    public function __construct($routes)
    {
        foreach ($routes as $key => $route) {
            $this->load($route, $key);
        }
        uasort($this->routes, array($this, 'sortByURL'));
    }

    public function route($url)
    {
        $route = $this->getRoute($url);
        if ($route) {
            $classController = '\Scoop\Controller';
            $controller = $route['controller'];
            if (!is_subclass_of($controller, $classController)) {
                throw new \UnexpectedValueException($controller.' not implement '.$classController);
            }
            $controller = \Scoop\Context::getInjector()->getInstance($controller);
            if ($controller) {
                $this->intercept($url);
                $controllerReflection = new \ReflectionClass($controller);
                $method = $this->getMethod($route);
                if (!$controllerReflection->hasMethod($method)) {
                    throw new \Scoop\Http\MethodNotAllowedException();
                }
                $method = $controllerReflection->getMethod($method);
                $numParams = count($route['params']);
                if (
                    $numParams >= $method->getNumberOfRequiredParameters() &&
                    $numParams <= $method->getNumberOfParameters()
                ) {
                    return $method->invokeArgs($controller, $route['params']);
                }
            }
        }
        throw new \Scoop\Http\NotFoundException();
    }

    public function intercept($url)
    {
        $matches = $this->filterProxy($url);
        $injector = \Scoop\Context::getInjector();
        foreach ($matches as $route) {
            $proxy = $injector->getInstance($route['proxy']);
            $proxy->execute($url);
        }
    }

    public function getURL($key, $params, $query)
    {
        $path = preg_split('/\{\w+\}/', $this->routes[$key]['url']);
        $url = array_shift($path);
        $count = count($path);
        if (count($params) > $count) {
            throw new \InvalidArgumentException('Unformed URL');
        }
        for ($i = 0; $i < $count; $i++) {
            if (isset($params[$i])) {
                $url .= self::encodeURL($params[$i]).$path[$i];
            }
        }
        if (strrpos($url, '/') !== strlen($url)-1) {
            $url .= '/';
        }
        return ROOT.substr($url, 1).$this->formatQueryString($query);
    }

    public function formatQueryString($query)
    {
        if (!is_array($query)) return '';
        $queryString = '';
        foreach ($query AS $name => $value) {
            if ($value) {
                $queryString .= '&'.$name.'='.$value;
            }
        }
        return $queryString ? '?'.substr($queryString, 1) : '';
    }

    public function getCurrentRoute()
    {
        return $this->current;
    }

    private function getMethod($route)
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (isset($route['methods'])) {
            $methods = $route['methods'];
            if (isset($methods[$method])) {
                return $methods[$method];
            }
            if (isset($methods['all'])) {
                return $methods['all'];
            }
        }
        return $method;
    }

    private function getRoute($url)
    {
        $matches = $this->filterRoute($url);
        if ($matches) {
            $route = end($matches);
            $this->current = key($matches);
            array_shift($route['params']);
            $lenght = 0;
            foreach ($route['params'] as $key => $param) {
                if ($param !== '') {
                    $param = urldecode($param);
                    $lenght = ++$key;
                }
            }
            $route['params'] = array_splice($route['params'], 0, $lenght);
            return $route;
        }
    }

    private function filterRoute($url)
    {
        $matches = array();
        foreach ($this->routes as $key => $route) {
            if (
                isset($route['controller']) &&
                preg_match('/^'.self::normalizeURL($route['url']).'$/', $url, $route['params'])
            ) {
                $matches[$key] = $route;
            }
        }
        return $matches;
    }

    private function filterProxy($url)
    {
        $matches = array();
        foreach ($this->routes as $route) {
            if (
                isset($route['proxy']) &&
                preg_match('/^'.self::normalizeURL($route['url']).'/', $url)
            ) {
                $matches[] = $route;
            }
        }
        return $matches;
    }

    private function load($route, $key, $oldURL = '')
    {
        if (!isset($route['url'])) {
            throw new \OutOfBoundsException('url\'s key has not been defined for the route');
        }
        $route['url'] = $oldURL.$route['url'];
        if (isset($route['routes'])) {
            $routes = $route['routes'];
            if (is_string($routes)) {
                $routes = \Scoop\Context::getService('config')->load($routes);
                if (is_string($routes)) {
                    throw new \InvalidArgumentException('routes '.$routes.' not supported');
                }
            }
            foreach ($routes as $k => $r) {
                $this->load($r, $k, $route['url']);
            }
            unset($route['routes']);
        }
        $this->routes[$key] = $route;
    }

    private static function sortByURL($a, $b)
    {
        return strcasecmp($a['url'], $b['url']) < 0;
    }

    private static function normalizeURL($url)
    {
        $url = str_replace(array(
            '/{var}/',
            '/{int}/',
            '{var}',
            '{int}'
        ), array(
            '/([\w\+\-\s\.]*)/?',
            '/(\d*)/?',
            '([\w\+\-\s\.]*)',
            '(\d*)'
        ),$url).((substr($url, -1) === '/')? '?': '/?');
        return addcslashes($url, '/');
    }

    private static function encodeURL($str)
    {
        $str = mb_strtolower($str);
        $str = str_replace(
            array('á', 'à', 'ä', 'â', 'ª'), 'a', $str
        );
        $str = str_replace(
            array('é', 'è', 'ë', 'ê'), 'e', $str
        );
        $str = str_replace(
            array('í', 'ì', 'ï', 'î'), 'i', $str
        );
        $str = str_replace(
            array('ó', 'ò', 'ö', 'ô'), 'o', $str
        );
        $str = str_replace(
            array('ú', 'ù', 'ü', 'û'), 'u', $str
        );
        $str = str_replace(
            array(' ', 'ñ', 'ç'), array('-', 'n', 'c'), $str
        );
        return urlencode($str);
    }
}
