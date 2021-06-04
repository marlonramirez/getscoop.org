<?php
namespace App\Controller;

class Index extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('home');
        return $view->set('title', 'Simple Characteristics of Object-Oriented PHP');
    }
}
