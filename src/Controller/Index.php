<?php

namespace App\Controller;

use Scoop\Bootstrap\Environment;

class Index
{
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return \Scoop\View
     */
    public function get()
    {
        $view = new \Scoop\View('home');
        return $view->add('title', $this->environment->getConfig('app.description'));
    }
}
