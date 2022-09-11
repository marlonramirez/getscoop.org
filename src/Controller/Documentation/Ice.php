<?php
namespace App\Controller\Documentation;

class Ice extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/folder');
        return $view->set('title', 'Entorno de linea de comandos');
    }
}
