<?php
namespace App\Controller\Documentation;

class Configure extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/configure');
        return $view->set('title', 'Configuración del proyecto');
    }
}
