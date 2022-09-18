<?php
namespace App\Controller\Documentation;

class Resource extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/resource');
        return $view->set('title', 'Manejo de rescursos y assets');
    }
}
