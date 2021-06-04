<?php
namespace App\Controller\Documentation;

class View extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/views');
        return $view->set('title', 'Vistas y plantillas');
    }
}
