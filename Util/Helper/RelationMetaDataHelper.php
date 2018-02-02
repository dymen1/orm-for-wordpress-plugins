<?php
namespace Dorans\Competition\Util\Helper;

use Dorans\Competition\Util\EntityMetaData;

abstract class RelationMetaDataHelper
{
    protected static $tagName = 'relation';

    const ONE_TO_MANY = 'OneToMany';
    const MANY_TO_ONE = 'ManyToOne';
    const MANY_TO_MANY = 'ManyToMany';
    const ONE_TO_ONE = 'OneToOne';

    protected static $relationTypes = array(
        self::ONE_TO_MANY,
        self::MANY_TO_ONE,
        self::MANY_TO_MANY,
        self::ONE_TO_ONE,
    );

    const LAZY = 'lazy';
    const EAGER = 'eager';

    protected static $relationModes = array(
        self::LAZY,
        self::EAGER,
    );

    /**
     * @var EntityMetaData
     */
    protected static $entityMetaData;

    /**
     * Get Entity relations from it's properties
     * example:
     * '@relation({"type": "OneToMany", "class": "Dorans\Competition\Entity\Player"})'
     *
     * @param EntityMetaData $entityMetaData
     * @return array
     */
    public static function getRelations(EntityMetaData $entityMetaData)
    {
        self::$entityMetaData = $entityMetaData;
        $entityClassName = $entityMetaData->getEntityClassName();
        $reflector = $entityMetaData->getReflectionClass($entityClassName);

        $relations = array();
        /** @var \ReflectionProperty $property */
        foreach ($reflector->getProperties() as $property) {
            $doc = $property->getDocComment();
            preg_match('/\@' . self::$tagName . '\((.*)\)/', $doc, $matches);
            if (empty($matches)) {
                continue;
            }
            $relationParams = json_decode(str_replace('\\', '\\\\', $matches[1]));

            self::validateRelationParams($property->getName(), $relationParams);
            $relations[$property->getName()] = $relationParams;
        }
        return $relations;
    }

    /**
     * TODO: refactor -> create different and better defined flows for relation types
     * validates relation params
     * type : required
     * type of the relation
     *
     * class : required
     * target class this class has a relation with
     *
     * mappedBy : defaults to lcfirst(shortClassName)
     * property in target class that owns the relation
     * (keeps track of it)
     *
     * mode : defaults to lazy
     * mode in which the relation is loaded
     *
     * @param $relationName
     * @param $params
     * @throws \Exception
     */
    protected static function validateRelationParams($relationName, $params)
    {
        if (!property_exists($params, 'type') || !in_array($params->type, self::getRelationTypes())) {
            self::throwRelationValidationException($relationName, 'type');
        }

        if (!property_exists($params, 'class') || !class_exists($params->class)) {
            self::throwRelationValidationException($relationName, 'class');
        }

        // TODO: discuss if this holds true and if mode should be excluded to
        // next part is currently only needed for XxToMany
        $stringToMatch = 'Many';
        if (substr($params->type, -strlen($stringToMatch)) !== $stringToMatch) {
            // tested for ManyToOne
            if (!property_exists($params, 'mappedBy')) {
                $params->mappedBy = 'id';
            }
            if (!property_exists($params->class, $params->mappedBy)) {
                self::throwRelationValidationException($relationName, 'mappedBy');
            }

            if (!property_exists($params, 'inversedBy')) {
                $params->inversedBy = $relationName;
            }
            if (!property_exists(self::$entityMetaData->getEntityClassName(), $params->inversedBy)) {
                self::throwRelationValidationException($relationName, 'inversedBy');
            }
            $params->inversedBy .= '_id';

            return;
        }

        if (!property_exists($params, 'mappedBy')) {
            $params->mappedBy = lcfirst(self::$entityMetaData->getReflectionClass(self::$entityMetaData->getEntityClassName())->getShortName());
            if (!property_exists($params->class, $params->mappedBy)) {
                self::throwRelationValidationException($relationName, 'mappedBy');
            }
            $params->mappedBy .= '_id';
        } else {
            if (!property_exists($params->class, $params->mappedBy)) {
                self::throwRelationValidationException($relationName, 'mappedBy');
            }
        }

        if (!property_exists($params, 'mode')) {
            $params->mode = self::LAZY;
        }
        if (!in_array($params->mode, self::getRelationModes())) {
            self::throwRelationValidationException($relationName, 'mode');
        }
    }

    protected static function throwRelationValidationException($relationName, $message)
    {
        throw new \Exception('Class ' . self::$entityMetaData->getEntityClassName() . '::' . $relationName . ' has invalid relation param: "' . $message . '"');
    }

    protected static function getRelationTypes()
    {
        return self::$relationTypes;
    }

    protected static function getRelationModes()
    {
        return self::$relationModes;
    }
}