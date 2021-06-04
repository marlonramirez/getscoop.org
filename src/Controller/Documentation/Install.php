<?php
namespace App\Controller\Documentation;

class Install extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/install');
        return $view->set('title', 'Instalación del bootstrap');
    }
}
