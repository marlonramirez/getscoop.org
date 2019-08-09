<?php
namespace Scoop\Bootstrap;

class Environment
{
    private static $sessionInit = false;
    private $router;
    private $config;

    public function __construct($configPath)
    {
        if (!self::$sessionInit) {
            self::$sessionInit = session_start();
        }
        define('ROOT', '//'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/');
        $this->config = require $configPath.'.php';
    }

    public function get($name)
    {
        $name = explode('.', $name);
        $res = $this->config;
        foreach ($name as $key) {
            if (!isset($res[$key])) return null;
            $res = $res[$key];
        }
        return $res;
    }

    public function route($url)
    {
        $this->configure();
        return $this->router->route($url);
    }

    public function getURL($args)
    {
        $query = array_pop($args);
        if (!is_array($query)) {
            array_push($args, $query);
            $query = null;
        }
        if (empty($args)) {
            $currentPath = '//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            if ($query) {
                return $this->mergeQueryString($currentPath, $query);
            }
            return $currentPath;
        }
        return $this->router->getURL(array_shift($args), $args, $query);
    }

    public function isCurrentRoute($route)
    {
        return $this->router->getCurrentRoute() === $route;
    }

    protected function configure() {
        \Scoop\Validator::setMessages((Array) $this->get('messages.error'));
        \Scoop\Validator::addRule((Array) $this->get('validators'));
        $this->router = new \Scoop\IoC\Router((Array) $this->get('routes'));
        $this->bind((Array) $this->get('providers'));
        $this->registerComponents((Array) $this->get('components'));
        $services = (Array) $this->get('services');
        $services += array('config' => $this, 'request' => new \Scoop\Http\Request());
        $this->registerServices($services);
        return $this;
    }

    protected function bind($interfaces)
    {
        $injector = \Scoop\Context::getInjector();
        foreach ($interfaces as $interface => $class) {
            $injector->bind($interface, $class);
        }
        return $this;
    }

    protected function registerServices($services)
    {
        foreach ($services as $name => $service) {
            is_array($service) ?
                \Scoop\Context::registerService($name, array_shift($service), $service) :
                \Scoop\Context::registerService($name, $service);
        }
        return $this;
    }

    protected function registerComponents($components)
    {
        foreach ($components as $name => $component) {
            \Scoop\View::registerComponent($name, $component);
        }
        return $this;
    }

    private function mergeQueryString($currentPath, $query)
    {
        $path = explode('?', $currentPath);
        $currentPath = array_shift($path);
        $params = array_shift($path);
        $path = array();
        if ($params) {
            $params = explode('&', $params);
            foreach ($params AS $param) {
                $param = explode('=', $param);
                $path[$param[0]] = $param[1];
            }
        }
        $query += $path;
        $queryString = '';
        foreach ($query AS $name => $value) {
            $queryString .= $name.'='.$value;
        }
        return $currentPath.'?'.$queryString;
    }
}
