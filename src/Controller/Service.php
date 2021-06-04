<?php
namespace App\Controller;

class Service extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('services');
        return $view->set('title', 'Oferta de servicios');
    }
}
