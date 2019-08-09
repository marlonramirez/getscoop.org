<?php
use App\Controller\Index;

return array(
    'home' => array(
        'controller' => Index::class,
        'url' => '/'
    ),
    'about' => array(
        'controller' => Index::class.':about',
        'url' => '/about/'
    ),
    'services' => array(
        'controller' => Index::class.':services',
        'url' => '/services/',
    ),
    'doc' => array(
        'url' => '/docs/',
        'controller' => 'App\Controller\Documentation',
        'routes' => require 'routes/documentation.php'
    )
);
