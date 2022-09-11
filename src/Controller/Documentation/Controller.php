<?php
namespace App\Controller\Documentation;

class Controller extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/controllers');
        return $view->set('title', 'Ciclo de vida de una petición');
    }
}
