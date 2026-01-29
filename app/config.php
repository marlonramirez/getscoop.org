<?php
return array(
    'app' => 'json:package',
    'providers' => require 'config/providers.php',
    'messages' => array(
        'es' => 'import:app/config/lang/es',
        'en' => 'import:app/config/lang/en'
    ),
    'cors' => true
);
