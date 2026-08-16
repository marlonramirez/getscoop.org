<?php

namespace Scoop\Persistence\Entity\Query;

use Scoop\Persistence\Entity\Mapper\Discriminator;
use Scoop\Persistence\Entity\Resolver\Field;

class Plan
{
    private $fields;
    private $joins;
    private $discriminator;

    public function __construct($root, $map, $mapper, $builder)
    {
        $fieldResolver = new Field($map, $mapper);
        $fieldResolver->addFields($root, 'r', false);
        $this->discriminator = new Discriminator($root, $map['entities'], $builder);
        $discriminatorColumn = $this->discriminator->getColumn();
        if ($discriminatorColumn) {
            $fieldResolver->addRawField($discriminatorColumn, 'r.' . $discriminatorColumn);
        }
        $this->fields = $fieldResolver->getFields();
        $this->joins = $fieldResolver->getJoins();
    }

    public function createFieldResolver($map, $mapper)
    {
        return new Field($map, $mapper, $this->fields, $this->joins);
    }

    public function getDiscriminator()
    {
        return $this->discriminator;
    }
}
