<?php
namespace App\Controller\Documentation;

class Monitor extends \Scoop\Controller
{
    public function get()
    {
        $view = new \Scoop\View('documentation/monitor');
        return $view->set('title', 'Monitoreo y Logging');
    }
}
