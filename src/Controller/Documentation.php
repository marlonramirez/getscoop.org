<?php

namespace App\Controller;

class Documentation
{
    /**
     * @var array<string|int, array{title: string, view: string}>
     */
    private static $views = array(
        0 => array('title' => 'Fundamentos', 'view' => 'intro'),
        'core' => array('title' => 'El motor (Engine)', 'view' => 'core'),
        'routing' => array('title' => 'Infraestructura y ruteo', 'view' => 'routing'),
        'application' => array('title' => 'Capa de aplicación', 'view' => 'application'),
        'persistence' => array('title' => 'Persistencia Atómica', 'view' => 'persistence'),
        'domain' => array('title' => 'Modelado de Dominio', 'view' => 'models'),
        'presentation' => array('title' => 'Capa de entrega (sdt)', 'view' => 'views'),
        'ecosystem' => array('title' => 'Ecosistema y herramientas', 'view' => 'ecosystem'),
        'ops' => array('title' => 'Calidad y despliegue', 'view' => 'deploy')
    );

    /**
     * @param string $name
     * @throws \Scoop\Http\Exception\NotFound
     * @return \Scoop\View
     */
    public function get($name = null)
    {
        $view = new \Scoop\View('layers/docs');
        if ($name === null) {
            return $view->add(self::$views[0] + array('menu' => self::$views));
        }
        if ($name === '0' || !isset(self::$views[$name])) {
            throw new \Scoop\Http\Exception\NotFound();
        }
        return $view->add(self::$views[$name] + array('menu' => self::$views));
    }
}
