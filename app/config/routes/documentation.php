<?php
use App\Controller\Documentation\{
    Controller,
    Configure,
    View,
    Model,
    Folder,
    Ice,
    Monitor,
    Resource
};

return array(
    'doc-config' => array(
        'url' => 'configure/',
        'controller' => Configure::class
    ),
    'doc-controller' => array(
        'url' => 'controllers/',
        'controller' => Controller::class
    ),
    'doc-view' => array(
        'url' => 'views/',
        'controller' => View::class
    ),
    'doc-model' => array(
        'url' => 'models/',
        'controller' => Model::class
    ),
    'doc-folder' => array(
        'url' => 'folders/',
        'controller' => Folder::class
    ),
    'doc-ice' => array(
        'url' => 'ice/',
        'controller' => Ice::class
    ),
    'doc-monitoring' => array(
        'url' => 'monitoring/',
        'controller' => Monitor::class
    ),
    'doc-resources' => array(
        'url' => 'resources/',
        'controller' => Resource::class
    )
);
