<?php
namespace App\Controller\Documentation;

class Resource extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/resource');
        return $view->set('title', 'Entorno de linea de comandos');
    }
}
