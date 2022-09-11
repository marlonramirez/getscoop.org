<?php
namespace App\Controller\Documentation;

class Folder extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/folder');
        return $view->set('title', 'Estructura de carpetas');
    }
}
