<?php
namespace App\Controller\Documentation;

class SQO extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/sqo');
        return $view->set('title', 'Persistencia con SQO');
    }
}
