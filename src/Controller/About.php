<?php

namespace App\Controller;

class About extends \Scoop\Controller
{
    /**
     * @return \Scoop\View
     */
    public function get()
    {
        $view = new \Scoop\View('about');
        return $view->set('title', 'Acerca del proyecto');
    }

    /**
     * @return array{out: string}
     */
    public function post()
    {
        return ['out' => 'Gracias por comunicarse con nosotros'];
    }
}
