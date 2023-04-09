<?php
use App\Controller\{
    Index,
    Service,
    About,
    Documentation
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
        'controller' => Documentation::class,
        'url' => '/docs/{var}'
    )
);
