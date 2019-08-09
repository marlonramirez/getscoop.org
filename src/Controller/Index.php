<?php
namespace App\Controller;

class Index extends \Scoop\Controller {
    public function services()
    {
        $view = new \Scoop\View('services');
        return $view->set('title', 'Oferta de servicios');
    }

    public function about()
    {
        $view = new \Scoop\View('about');
        return $view->set('title', 'Acerca del proyecto');
    }

    public function get()
    {
        $view = new \Scoop\View('home');
        return $view->set('title', 'Simple Characteristics of Object-Oriented PHP');
    }
}
