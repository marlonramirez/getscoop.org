<?php
use App\Controller\Documentation;

return array(
    'doc-config' => array(
        'url' => 'configure/',
        'controller' => Documentation::class,
        'methods' => array(
            'get' => 'configure'
        )
    ),
    'doc-controller' => array(
        'url' => 'controllers/',
        'controller' => Documentation::class,
        'methods' => array(
            'get' => 'controllers'
        )
    ),
    'doc-view' => array(
        'url' => 'views/',
        'controller' => Documentation::class,
        'methods' => array(
            'get' => 'views'
        )
    ),
    'doc-model' => array(
        'url' => 'models/',
        'controller' => Documentation::class,
        'methods' => array(
            'get' => 'models'
        )
    )
);
