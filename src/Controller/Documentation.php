<?php
namespace App\Controller;

class Documentation extends \Scoop\Controller
{
    private static $views = array(
        0 => array('title' => 'Iniciando', 'view' => 'intro'),
        'configure' => array('title' => 'Configuración', 'view' => 'configure'),
        'architecture' => array('title' => 'Arquitectura', 'view' => 'architecture'),
        'ddd' => array('title' => 'Diseño de dominio', 'view' => 'models'),
        'frontend' => array('title' => 'Front-end', 'view' => 'views'),
        'resources' => array('title' => 'Recursos', 'view' => 'resource'),
        'deploy' => array('title' => 'Despliegue', 'view' => 'deploy')
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