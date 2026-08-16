<?php

namespace Scoop\Persistence\Entity\Mapper;

class Plan
{
    private $reflectionClasses = array();
    private $plans = array();

    public function createObject($className)
    {
        if (!isset($this->reflectionClasses[$className])) {
            $this->reflectionClasses[$className] = new \ReflectionClass($className);
        }
        return $this->reflectionClasses[$className]->newInstanceWithoutConstructor();
    }

    public function get($className, $fields, $entityMap)
    {
        if (isset($this->plans[$className])) {
            foreach ($this->plans[$className] as $cached) {
                if ($cached['fields'] === $fields) {
                    return $cached['plan'];
                }
            }
        }
        $plan = array();
        $properties = $entityMap[$className]['properties'];
        foreach ($fields as $name => $column) {
            if (!is_string($column)) continue;
            $vo = explode('.', $column);
            if (isset($vo[1])) {
                $snapshotColumn = $vo[1];
                if (preg_match('/([^\$]+)\$v\$(.*)/', $name, $match)) {
                    $property = \Scoop\Persistence\Entity\Mapper::toProperty($match[1]);
                    $valueProperty = \Scoop\Persistence\Entity\Mapper::toProperty($match[2]);
                } else {
                    $property = \Scoop\Persistence\Entity\Mapper::toProperty($vo[1]);
                    $valueProperty = 'value';
                }
            } else {
                $property = \Scoop\Persistence\Entity\Mapper::toProperty($column);
                $snapshotColumn = isset($properties[$property]['column']) ?
                    $properties[$property]['column'] : $column;
                $valueProperty = null;
            }
            $plan[] = array(
                'name' => $name,
                'property' => $property,
                'column' => $snapshotColumn,
                'type' => $properties[$property]['type'],
                'valueProperty' => $valueProperty
            );
        }
        $this->plans[$className][] = array('fields' => $fields, 'plan' => $plan);
        return $plan;
    }
}
