<?php
use App\Controller\{
    Index,
    Documentation
};

return array(
    'home' => array(
        'controller' => Index::class,
        'url' => '/'
    ),
    'doc' => array(
        'controller' => Documentation::class,
        'url' => '/docs/{var}'
    )
);
