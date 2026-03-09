<?php

namespace App\Command;

interface Command
{
    public static function getName();

    public function execute();

    public function help();
}
