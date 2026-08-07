<?php

namespace Scoop\Container;

abstract class Injector
{
    private $rules = array();
    private $providerMaps = array();
    private $environment;

    public function __construct($environment)
    {
        $this->environment = $environment;
        $this->setInstance('Scoop\Bootstrap\Environment', $environment);
        $this->bind($environment->getConfig('providers', array()));
    }

    public static function formatClassName($className)
    {
        if (strpos($className, '\\') === 0) {
            return substr($className, 1);
        }
        return $className;
    }

    abstract public function has($id);

    abstract protected function getInstance($id);

    abstract protected function setInstance($id, $instance);

    public function get($id)
    {
        $id = self::formatClassName($id);
        if (!$this->has($id)) {
            if (isset($this->rules[$id])) {
                return $this->create($this->rules[$id], $id);
            }
            return $this->create($id);
        }
        return $this->getInstance($id);
    }

    public function create($id, $inheritance = null)
    {
        $method = explode(':', $id);
        $instance = $this->instantiate($method[0], isset($method[1]) ? $method[1] : null);
        if ($inheritance) {
            if (!is_a($instance, $inheritance) && !is_subclass_of($instance, $inheritance)) {
                $className = get_class($instance);
                throw new \Scoop\Container\Exception("Object of type $className does not instance of $inheritance", 1105);
            }
            $id = $inheritance;
        }
        $this->setInstance($id, $instance);
        return $instance;
    }

    private function instantiate($className, $method)
    {
        if (!class_exists($className)) {
            throw new \Scoop\Container\Exception\NotFound("Class $className not found");
        }
        $providers = $this->getDefinition($className);
        if (empty($providers)) {
            $instance = new $className();
        } else {
            $class = new \ReflectionClass($className);
            $instance = $class->newInstanceArgs(array_map(function ($provider) {
                return \Scoop\Context::inject($provider);
            }, $providers));
        }
        if ($method) {
            if (!is_callable(array($instance, $method))) {
                throw new \Scoop\Container\Exception("Factory method $className:$method not found");
            }
            $instance = $instance->$method();
            if (!is_object($instance)) {
                $type = gettype($instance);
                throw new \Scoop\Container\Exception(
                    "Factory method $className:$method returned $type and must resolve to an object instance."
                );
            }
        }
        return $instance;
    }

    private function bind($interfaces)
    {
        foreach ($interfaces as $interfaceName => $className) {
            $interfaceName = self::formatClassName($interfaceName);
            $className = self::formatClassName($className);
            $this->rules[$interfaceName] = $className;
        }
    }

    private function getDefinition($className)
    {
        $providerPath = $this->environment->getStoragePath('cache/project');
        if (!is_readable("{$providerPath}Scoop_providers.php")) {
            return $this->getReflectionDefinition($className);
        }
        $normalizedName = $providerPath . str_replace('\\', '_', $className);
        $providerFilePath = null;
        $providerPrefixLength = 0;
        $providerFiles = glob("{$providerPath}*providers.php");
        foreach ($providerFiles as $filePath) {
            $prefix = substr($filePath, 0, -13);
            if (strpos($normalizedName, $prefix) === 0 && strlen($prefix) > $providerPrefixLength) {
                $providerFilePath = $filePath;
                $providerPrefixLength = strlen($prefix);
            }
        }
        if (!$providerFilePath) {
            throw new \Scoop\Container\Exception("Provider map for $className not found", 1101);
        }
        if (!isset($this->providerMaps[$providerFilePath])) {
            $this->providerMaps[$providerFilePath] = require $providerFilePath;
        }
        if (!array_key_exists($className, $this->providerMaps[$providerFilePath])) {
            throw new \Scoop\Container\Exception("Providers for $className not found", 1101);
        }
        return $this->providerMaps[$providerFilePath][$className];
    }

    private function getReflectionDefinition($className)
    {
        $class = new \ReflectionClass($className);
        if (!$class->isInstantiable()) {
            throw new \Scoop\Container\Exception("Providers for $className not found", 1101);
        }
        $constructor = $class->getConstructor();
        if (!$constructor) {
            return array();
        }
        $providers = array();
        $usesDefault = false;
        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            $provider = $this->getParameterClass($parameter, $class);
            $isDefault = $parameter->isDefaultValueAvailable();
            if ($provider && !$usesDefault) {
                $providers[] = $provider;
                continue;
            }
            if (!$provider && $isDefault) {
                $usesDefault = true;
                continue;
            }
            throw new \Scoop\Container\Exception("Providers for $className not found", 1101);
        }
        return $providers;
    }

    private function getParameterClass($parameter, $class)
    {
        if (!method_exists($parameter, 'getType')) {
            $provider = $parameter->getClass();
            return $provider ? $provider->getName() : null;
        }
        $type = $parameter->getType();
        if (!$type || !method_exists($type, 'isBuiltin') || $type->isBuiltin()) {
            return null;
        }
        $provider = method_exists($type, 'getName') ? $type->getName() : (string) $type;
        if ($provider === 'self') {
            return $class->getName();
        }
        if ($provider === 'parent') {
            $parent = $class->getParentClass();
            return $parent ? $parent->getName() : null;
        }
        return ltrim($provider, '\\');
    }
}
