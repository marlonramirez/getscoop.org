<?php
use App\Controller\Documentation\{
    Index,
    Controller,
    Configure,
    View,
    Model
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
    )
);
