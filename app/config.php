<?php
return array(
    'app' => 'json:package',
    'messages' => require 'config/messages.php',
    'routes' => require 'config/routes.php',
    'db' => array(
        'default' => array(
            'database' => 'scoop',
            'user' => 'postgres',
            'password' => 'postgres',
            'host' => 'localhost',
            'driver' => 'pgsql'
        )
    )
);
