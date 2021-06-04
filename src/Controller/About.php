<?php
namespace App\Controller;

class About extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('about');
        return $view->set('title', 'Acerca del proyecto');
    }

    public function post() {
        return ['out' => 'Gracias por comunicarse con nosotros'];
    }
}
