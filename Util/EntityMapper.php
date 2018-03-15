<?php
namespace Dorans\Competition\Util;

use Dorans\Competition\Entity\Base\AbstractEntity;
use Dorans\Competition\Entity\Player;

class EntityMapper
{
    protected $blackListedProperties = array(
        'tableName'
    );

    /**
     * @var EntityMetaData
     */
    protected $entityMetaData;

    /**
     * EntityMapper constructor.
     * @param $entityClassName
     */
    public function __construct($entityClassName)
    {
        $this->entityMetaData = new EntityMetaData($entityClassName);
    }

    /**
     * TODO: refactor
     * Hydrates associative array to object
     *
     * @param $data
     * @return mixed
     */
    public function hydrate($data)
    {
        $entityMetaData = $this->getEntityMetaData();
        $entityClassName = $entityMetaData->getEntityClassName();
        $entity = new $entityClassName();
        foreach ($data as $name => $value) {
            $setter = $this->getSetter($name, $entity);
            if (is_null($setter)) {

                $setter = $this->getSetter($name, $entity, 'add');
                if (!is_null($setter) && is_array($value)) {
                    $relationalEntityClass = $entityMetaData->getRelations()[$name]->class;
                    $relationalEntityMapper = new EntityMapper($relationalEntityClass);
                    if (!empty($_POST)) {
                        if (array_key_exists('removed', $value)) {
                            $removedEntities = $value['removed'];
                            // TODO: when I implement a UoW add $removedEntities to the remove queue
                            unset($value['removed']);
                        }
                    }
                    $value = $relationalEntityMapper->hydrateCollection($value);
                } else {
                    // TODO: log cases that get here
                    continue;
                }
            } elseif (is_array($value)) {
                if (count($value) === 1) {
                    $relationalEntityClass = $entityMetaData->getRelations()[$name]->class;
                    $relationalEntityMapper = new EntityMapper($relationalEntityClass);

                    $value = $relationalEntityMapper->hydrate(reset($value));
                } else {
                    $value = null;
                }
            }
            $entity->$setter($value);
        }

        return $entity;
    }

    /**
     * TODO: might be slow AF
     * Gets setter by checking for method
     *
     * @param $name
     * @param $entity
     * @param string $fnPrefix
     * @return mixed|string
     */
    protected function getSetter($name, $entity, $fnPrefix = 'set')
    {
        $setter = $fnPrefix . ucfirst($name);
        if (!method_exists($entity, $setter)) {

            $idSuffix = '_id';
            if (substr($setter, -strlen($idSuffix)) === $idSuffix) {
                $setter = str_replace($idSuffix, '', $setter);
                return $setter;
            } else {

                $setter = $this->underscoreToCamelCase($setter);
                if (!method_exists($entity, $setter)) {
                    return null;
                }
                return $setter;
            }
        }
        return $setter;
    }

    /**
     * @param array $data
     * @return array
     */
    public function hydrateCollection(array $data)
    {
        $returnArray = array();
        foreach ($data as $row) {
            $returnArray[] = $this->hydrate($row);
        }
        return $returnArray;
    }

    /**
     * Dehydrates object to associative array
     *
     * @param AbstractEntity $entity
     * @return array
     * @throws \Exception
     */
    public function dehydrate(AbstractEntity $entity)
    {
        $entityMetaData = $this->getEntityMetaData();
        $entityClassName = $entityMetaData->getEntityClassName();
        if (get_class($entity) !== $entityClassName) {
            throw new \Exception('Given entity is not an instance of "' . $entityClassName . '".');
        }

        $properties = (new \ReflectionClass($entityClassName))->getProperties();
        $assocArray = array();
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (in_array($propertyName, $this->blackListedProperties)) {
                continue;
            }

            $getter = 'get' . ucfirst($propertyName);
            if (method_exists($entity, $getter)) {
                $value = $entity->$getter();
                if (is_array($value) && $entityMetaData->hasRelation($propertyName)) {

                    if ($entityClassName === Player::class) { // TODO: test if ManyToMany cause recursion
                        echo '<pre>';
                        var_dump($getter);
                        var_dump($value);
                        die('looped');
                    }

                    $relationalMapper = new EntityMapper($entityMetaData->getRelations()[$propertyName]->class);
                    $array = array();
                    foreach ($value as $element) {
                        $array[] = $relationalMapper->dehydrate($element);
                    }
                    $value = $array;
                } elseif ($value instanceof \DateTime) {
                    $value = $value->format(DateTime::MYSQL_W_SECONDS);
                }

                $sanitizedPropertyName = strtolower(preg_replace('/\B([A-Z])/', '_$1', $propertyName));

                if ($entityMetaData->hasRelation($propertyName) &&
                    $entityMetaData->getRelations()[$propertyName]->type === 'ManyToOne'
                ) {
                    $sanitizedPropertyName .= '_id';
                    if (is_object($value)) {
                        $value = $value->getId();
                    }
                }

                $assocArray[$sanitizedPropertyName] = $value;
            }
        }
        return $assocArray;
    }

    /**
     * Convert strings with underscores into CamelCase
     *
     * @param string $string The string to convert
     * @param bool $first_char_caps camelCase or CamelCase
     * @return string The converted string
     *
     */
    protected function underscoreToCamelCase($string, $first_char_caps = false)
    {
        if ($first_char_caps === true) {
            $string[0] = strtoupper($string[0]);
        }
        $func = function($c) {
            return strtoupper($c[1]);
        };
        return preg_replace_callback('/_([a-z])/', $func, $string);
    }

    /**
     * @return EntityMetaData
     */
    public function getEntityMetaData()
    {
        return $this->entityMetaData;
    }
}