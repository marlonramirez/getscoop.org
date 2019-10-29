<?php
use App\Controller\Index;

return array(
    'home' => array(
        'controller' => Index::class,
        'url' => '/'
    ),
    'about' => array(
        'controller' => Index::class,
        'url' => '/about/',
        'methods' => array(
            'get' => 'about'
        )
    ),
    'services' => array(
        'controller' => Index::class,
        'url' => '/services/',
        'methods' => array(
            'get' => 'services',
            'post' => 'sendEmail'
        )
    ),
    'doc' => array(
        'url' => '/docs/',
        'controller' => 'App\Controller\Documentation',
        'routes' => require 'routes/documentation.php'
    )
);
