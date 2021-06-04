<?php
use App\Controller\{
    Index,
    Service,
    About
};

return array(
    'home' => array(
        'controller' => Index::class,
        'url' => '/'
    ),
    'about' => array(
        'controller' => About::class,
        'url' => '/about/'
    ),
    'services' => array(
        'controller' => Service::class,
        'url' => '/services/'
    ),
    'doc' => array(
        'url' => '/docs/',
        'controller' => 'App\Controller\Documentation\Index',
        'routes' => require 'routes/documentation.php'
    )
);
