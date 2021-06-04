<?php
namespace App\Controller\Documentation;

class Restful extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/restful');
        return $view->set('title', 'API RestFul');
    }
}
