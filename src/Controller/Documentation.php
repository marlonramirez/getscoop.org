<?php
namespace App\Controller;

class Documentation extends \Scoop\Controller {
    public function restful()
    {
        $view = new \Scoop\View('documentation/restful');
        return $view->set('title', 'API RestFul');
    }

    public function models()
    {
        $view = new \Scoop\View('documentation/models');
        return $view->set('title', 'Modelado de datos');
    }

    public function sqo()
    {
        $view = new \Scoop\View('documentation/sqo');
        return $view->set('title', 'Persistencia con SQO');
    }

    public function views()
    {
        $view = new \Scoop\View('documentation/views');
        return $view->set('title', 'Vistas y plantillas');
    }

    public function controllers()
    {
        $view = new \Scoop\View('documentation/controllers');
        return $view->set('title', 'Controladores');
    }

    public function configure()
    {
        $view = new \Scoop\View('documentation/configure');
        return $view->set('title', 'Configuración del proyecto');
    }

    public function install()
    {
        $view = new \Scoop\View('documentation/install');
        return $view->set('title', 'Instalación del bootstrap');
    }

    public function ioc()
    {
        $view = new \Scoop\View('documentation/ioc');
        return $view->set('title', 'Entornos y servicios');
    }

    public function get()
    {
        $view = new \Scoop\View('documentation/intro');
        return $view->set('title', 'documentación');
    }
}
