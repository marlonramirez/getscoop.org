<?php
namespace App\Controller\Documentation;

class Model extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/models');
        return $view->set('title', 'Modelado de datos');
    }
}
