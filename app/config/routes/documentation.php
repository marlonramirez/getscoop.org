<?php
use App\Controller\Documentation;

return array(
    'doc-config' => array(
        'url' => 'configure/',
        'controller' => Documentation::class.':configure'
    ),
    'doc-controller' => array(
        'url' => 'controllers/',
        'controller' => Documentation::class.':controllers'
    ),
    'doc-view' => array(
        'url' => 'views/',
        'controller' => Documentation::class.':views'
    ),
    'doc-model' => array(
        'url' => 'models/',
        'controller' => Documentation::class.':models'
    )
);
