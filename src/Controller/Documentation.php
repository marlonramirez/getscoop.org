<?php
namespace App\Controller;

class Documentation extends \Scoop\Controller
{
    private static $views = array(
        0 => array('title' => 'Iniciando con scoop', 'view' => 'intro'),
        'configure' => array('title' => 'Configuración del proyecto', 'view' => 'configure'),
        'folder' => array('title' => 'Estructura de carpetas', 'view' => 'folder'),
        'templates' => array('title' => 'Plantillas dinámicas', 'view' => 'views'),
        'ddd' => array('title' => 'Diseño de dominio', 'view' => 'models'),
        'life-cicle' => array('title' => 'Ciclo de vida', 'view' => 'controllers'),
        'resource' => array('title' => 'Recursos', 'view' => 'resource'),
        'monitoring' => array('title' => 'Monitoreo', 'view' => 'monitor'),
        'ice' => array('title' => 'CLI/ICE', 'view' => 'ice')
    );

    public function get($name = null)
    {
        $view = new \Scoop\View('layers/docs');
        if ($name === null) {
            return $view->set(self::$views[0] + array('menu' => self::$views));
        }
        if ($name === '0' || !isset(self::$views[$name])) {
            throw new \Scoop\Http\Exception\NotFound();
        }
        return $view->set(self::$views[$name] + array('menu' => self::$views));
    }
}