<?php
namespace Dorans\Competition\Util;

use Dorans\Competition\Util\Helper\RelationMetaDataHelper;

class EntityMetaData
{
    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @var \ReflectionClass[]
     */
    protected $reflectionClass = array();

    /**
     * @var array
     */
    protected $relations = null;

    /**
     * EntityMetaData constructor.
     * @param $entityClassName
     */
    public function __construct($entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @param $entityClassName
     * @return \ReflectionClass
     */
    public function getReflectionClass($entityClassName)
    {
        if (!isset($this->reflectionClass[$entityClassName])) {
            $this->reflectionClass[$entityClassName] = new \ReflectionClass($entityClassName);
        }

        return $this->reflectionClass[$entityClassName];
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        return $this->entityClassName;
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        if (is_null($this->relations)) {
            $this->relations = RelationMetaDataHelper::getRelations($this);
        }

        return $this->relations;
    }

    public function hasRelation($property) {
        return array_key_exists($property, $this->getRelations());
    }
}