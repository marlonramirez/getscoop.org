<?php
namespace App\Controller\Documentation;

class Index extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/intro');
        return $view->set('title', 'documentación');
    }
}
