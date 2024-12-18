<?php

namespace Scoop\Persistence\Entity\Type;

class Date
{
    public function disassemble($value)
    {
        return $value->format('Y-m-d H:i:s');
    }

    public function assemble($value)
    {
        return new \DateTime($value);
    }
}
