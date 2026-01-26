<?php

namespace App\Controller;

class Index
{
    /**
     * @return \Scoop\View
     */
    public function get()
    {
        $view = new \Scoop\View('home');
        return $view->add('title', 'Simple Characteristics of Object-Oriented PHP');
    }
}
