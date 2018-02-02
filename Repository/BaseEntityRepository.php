<?php

namespace Dorans\Competition\Repository;

use Dorans\Competition\Entity\Base\AbstractEntity;
use Dorans\Competition\Util\DateTime;
use Dorans\Competition\Util\EntityMapper;
use Dorans\Competition\Util\Helper\RelationMetaDataHelper;
use Dorans\Competition\Util\QueryBuilder;

/**
 * TODO: load all eager relations, currently only loads 1st level
 */
class BaseEntityRepository
{
    /**
     * wp's db connection class
     * TODO: think about adding a abstraction layer around the dbConn
     *
     * @var \wpdb
     */
    protected $dbConnection;

    /**
     * FQN of the target entity
     *
     * @var string
     */
    protected $entityClassName;

    /**
     * @var EntityMapper[]
     */
    protected $entityMapper = array();

    /**
     * BaseEntityRepository constructor.
     * @param \wpdb $dbConnection
     * @param null|string $entityClassName
     */
    public function __construct(\wpdb $dbConnection, $entityClassName = null)
    {
        $this->dbConnection = $dbConnection;
        $this->entityClassName = $entityClassName;
    }

    # region exposed functions

    /**
     * find entity by id
     *
     * @param $id
     * @param null $entityClassName
     * @return array|null
     * @throws \Exception
     */
    public function find($id, $entityClassName = null)
    {
        $entityClassName = $this->getEntityClassName($entityClassName);
        $entityTableName = $this->getEntityTableName($entityClassName);

        $qb = new QueryBuilder($entityClassName);
        $qb->addSelect('e0.*')
            ->from($entityTableName, 'e0');
        $this->addRelationJoinsToQueryBuilder($qb);
        $qb->addWhere('e0.id = ' . $id);

        $query = $qb->getQuery();
        $result = $this->dbConnection->get_results($query);

        if ($qb->hasJoins()) {
            $result = $this->formatResult($result);
            if (count($result) > 1) {
                throw new \Exception('Hydration went wrong..'); // notify Dylan when this happens plz
            }
        }

        return $this->assocArrayToObjectArray($result, $entityClassName);
    }

    /**
     * TODO: check if properties in criteria exist in object
     *
     * @param array $criteria
     * @param null $entityClassName
     * @return array|null
     * @throws \Exception
     */
    public function findBy(array $criteria, $entityClassName = null)
    {
        $entityClassName = $this->getEntityClassName($entityClassName);
        $entityTableName = $this->getEntityTableName($entityClassName);

        $qb = new QueryBuilder($entityClassName);
        $qb->addSelect('e0.*')
            ->from($entityTableName, 'e0');
        $this->addRelationJoinsToQueryBuilder($qb);
        foreach ($criteria as $property => $value) {
            $qb->addWhere("e0." . $property . " = " . $value);
        }

        $query = $qb->getQuery();
        $result = $this->dbConnection->get_results($query);

        if ($qb->hasJoins()) {
            $result = $this->formatResult($result);
        }

        return $this->assocArrayToObjectArray($result, $entityClassName, true);
    }

    /**
     * find all entities
     *
     * @param null $entityClassName
     * @return array|null
     * @throws \Exception
     */
    public function findAll($entityClassName = null)
    {
        $entityClassName = $this->getEntityClassName($entityClassName);
        $entityTableName = $this->getEntityTableName($entityClassName);

        $qb = new QueryBuilder($entityClassName);
        $qb->addSelect('e0.*')
            ->from($entityTableName, 'e0');
        $this->addRelationJoinsToQueryBuilder($qb);

        $query = $qb->getQuery();
        $result = $this->dbConnection->get_results($query);

        if ($qb->hasJoins()) {
            $result = $this->formatResult($result);
        }

        return $this->assocArrayToObjectArray($result, $entityClassName, true);
    }

    /**
     * persists given entity
     *
     * @param AbstractEntity $entity
     * @param null $entityClassName
     * @return false|int
     * @throws \Exception
     */
    public function persist(AbstractEntity $entity, $entityClassName = null)
    {
        $entityClassName = $this->getEntityClassName($entityClassName);
        $entityData = $this->objectToAssocArray($entity, $entityClassName);

        $entityDataArray = array($entityClassName => array($entityData));

        $relations = $this->getEntityMapper($entityClassName)->getEntityMetaData()->getRelations();
        $relations = $this->filterRelations($relations);
        foreach ($relations as $collectionName => $relationData) {
            // TODO: clean this up
            if (!array_key_exists($collectionName, $entityData)) {
                $collectionNameSuffixed = $collectionName . '_id';
                if (array_key_exists($collectionNameSuffixed, $entityData)) {
                    if (is_array($entityData[$collectionNameSuffixed])) {
                        throw new \Exception('Value of "' . $collectionNameSuffixed . '" cannot be array');
                    }
                }
                continue;
            }

            $collection = $entityData[$collectionName];

            unset($entityData[$collectionName]);
            $entityDataArray[$entityClassName][0] = $entityData;// TODO: what if main entity isn't at index 0?

            $this->tempFunctionForRemovingCollectionElements($collectionName, $relationData->class);

            if (empty($collection)) {
                continue;
            }

            if (!isset($entityDataArray[$relationData->class])) {
                $entityDataArray[$relationData->class] = array();
            }

            foreach ($collection as $element) {
                $entityDataArray[$relationData->class][] = $element;
            }
        }

        try {
            $result = true;
            foreach ($entityDataArray as $entityClassName => $entityCollection) {
                foreach ($entityCollection as $entityData) {

                    // TODO: correctly filter entity properties when array is given, but no relation is set for the given property
                    foreach ($entityData as $index => $dataRow) {
                        if (is_array($dataRow)) {
                            unset($entityData[$index]);
                        }
                    }

                    $entityTableName = $this->getEntityTableName($entityClassName);
                    if ($entityData['id'] > 0) {
                        if (isset($entityData['updated_at'])) {
                            $entityData['updated_at'] = (new DateTime())->format(DateTime::MYSQL_W_SECONDS);
                        }
                        $result = $this->dbConnection->update($entityTableName, $entityData,
                            array('id' => $entityData['id']));
                    } else {
                        $result = $this->dbConnection->insert($entityTableName, $entityData);
                    }
                }
            }
        } catch (\Exception $e) {
            $result = false;
            // TODO: remove
            echo '<pre>';
            var_dump($e);
            die('DONE');
            //
        }
        return $result;
    }

    protected function tempFunctionForRemovingCollectionElements($collectionName, $collectionEntityClass)
    {
        if (array_key_exists($collectionName, $_POST) && array_key_exists('removed', $_POST[$collectionName])) {
            foreach ($_POST[$collectionName]['removed'] as $removedElement) {
                $this->delete($removedElement, $collectionEntityClass);
            }
        }
    }

    /**
     * delete's entity by id
     * also accepts AbstractEntity object
     *
     * @param AbstractEntity|int|string $entity
     * @param null $entityClassName
     * @return false|int
     * @throws \Exception
     */
    public function delete($entity, $entityClassName = null)
    {
        $entityClassName = $this->getEntityClassName($entityClassName);
        $entityTableName = $this->getEntityTableName($entityClassName);

        if (!is_int($entity)) {
            if ($entity instanceof $entityClassName) {
                /** @var AbstractEntity $entity */
                $entity = $entity->getId();
            }

            if (!is_int($entity = (int)$entity)) {
                throw new \Exception('No valid id given..');
            }
        }

        if ($entity <= 0) {
            throw new \Exception('No "' . $entityClassName . '" with id ' . $entity . ' exists..');
        }

        $result = $this->dbConnection->delete($entityTableName, array('id' => $entity));
        return $result;
    }
    # endregion

    /**
     * @param $entityClassName
     * @return null
     * @throws \Exception
     */
    protected function getEntityClassName($entityClassName = null)
    {
        if ($entityClassName === null) {
            $entityClassName = $this->entityClassName;
        }

        if (!class_exists($entityClassName)) {
            throw new \Exception('"' . $entityClassName . '" does not exist.."');
        }

        return $entityClassName;
    }

    /**
     * @param array $resultArray
     * @param $entityClassName
     * @param bool|false $alwaysArray
     * @return array|null
     */
    protected function assocArrayToObjectArray(array $resultArray, $entityClassName, $alwaysArray = false)
    {
        if (empty($resultArray)) {
            return $alwaysArray ? array() : null;
        }

        $mappper = $this->getEntityMapper($entityClassName);

        if ($alwaysArray || count($resultArray) > 1) {
            return $mappper->hydrateCollection($resultArray);
        }

        return $mappper->hydrate(end($resultArray));
    }

    /**
     * @param AbstractEntity $entity
     * @param $entityClassName
     * @return array
     * @throws \Exception
     */
    protected function objectToAssocArray(AbstractEntity $entity, $entityClassName)
    {
        $mappper = $this->getEntityMapper($entityClassName);
        return $mappper->dehydrate($entity);
    }

    /**
     * @param $entityClassName
     * @return string
     */
    protected function getEntityTableName($entityClassName)
    {
        /** @var AbstractEntity $entity */
        $entity = new $entityClassName();
        return $this->dbConnection->prefix . $entity->getTableName();
    }

    /**
     * Convert strings with CamelCase into underscores
     *
     * @param string $string The string to convert
     * @return string The converted string
     *
     */
    protected function camelCaseToUnderscore($string)
    {
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $string));
    }

    /**
     * TODO: discuss if $relationField is unique enough
     *
     * @param QueryBuilder $qb
     */
    protected function addRelationJoinsToQueryBuilder(QueryBuilder $qb)
    {
        //TODO: centralize this
        $mappper = $this->getEntityMapper($this->getEntityClassName());
        $relations = $mappper->getEntityMetaData()->getRelations();

        $relations = $this->filterRelations($relations);

        $relationIndex = 0;
        foreach ($relations as $relationField => $relationData) {
            $alias = 'r' . $relationIndex;
            $joinTableName = $this->getEntityTableName($relationData->class);

            $columnQb = new QueryBuilder();
            $columnQb->addSelect('infoschema.COLUMN_NAME')
                ->from('information_schema.COLUMNS', 'infoschema')
                ->addWhere('infoschema.TABLE_NAME = \'' . $joinTableName . '\'');

            $columns = $this->dbConnection->get_results($columnQb->getQuery());
            foreach ($columns as $column) {
                $columnName = $column->COLUMN_NAME;
                $qb->addSelect($alias . '.' . $columnName . ' AS ' . $relationField . '_' . $columnName);
            }

            $joinPart = '';
            if ($relationData->type === RelationMetaDataHelper::MANY_TO_ONE) {
                $joinPart = $joinTableName . ' AS ' . $alias . ' ON e0.' . $relationData->inversedBy . ' = ' . $alias . '.' . $relationData->mappedBy;
            } else {
                $joinPart = $joinTableName . ' AS ' . $alias . ' ON e0.id = ' . $alias . '.' . $relationData->mappedBy;
            }
            $part = $joinPart;
            $qb->addJoin($part);

            $relationIndex++;
        }
    }

    /**
     * TODO: cleanup
     * TODO: support NULL
     *
     * @param $result
     * @return array
     */
    protected function formatResult($result)
    {
        //TODO: centralize this
        $mappper = $this->getEntityMapper($this->getEntityClassName());
        $relations = $mappper->getEntityMetaData()->getRelations();

        $uniqueSubjectEntityIds = array();
        $uniqueSubjectEntities = array();
        foreach ($result as $index => $row) {

            $relationalResultsArray = array();
            foreach ($relations as $relationField => $relationData) {// TODO: ManyToOne relation get mapped wrongly
                $columnPrefix = $relationField . '_';
                foreach ($row as $columnName => $columnValue) {
                    if (substr($columnName, 0, strlen($columnPrefix)) === $columnPrefix) {
                        unset($result[$index]->$columnName);
                        $relationalResultsArray[$relationField][str_replace($columnPrefix, '',
                            $columnName)] = $columnValue;
                    }
                }
            }

            if (!in_array($row->id, $uniqueSubjectEntityIds)) {
                $uniqueSubjectEntityIds[] = $row->id;
                $uniqueSubjectEntities[] = $row;
            }
            unset($result[$index]); // free up memory

            end($uniqueSubjectEntities); // moves key to end of array
            foreach ($relationalResultsArray as $relationField => $relationalResultArray) {
                $subjectEntity = key($uniqueSubjectEntities);
                if (!isset($uniqueSubjectEntities[$subjectEntity]->$relationField)) {
                    $uniqueSubjectEntities[$subjectEntity]->$relationField = array();
                }

                if (array_key_exists('id', $relationalResultArray) && is_null($relationalResultArray['id'])) {
                    continue;
                }
                array_push($uniqueSubjectEntities[$subjectEntity]->$relationField, $relationalResultArray);
            }
        }
        $result = $uniqueSubjectEntities;
        return $result;
    }

    /**
     * @param $entityClassName
     * @return EntityMapper
     */
    public function getEntityMapper($entityClassName)
    {
        if (!isset($this->entityMapper[$entityClassName])) {
            $this->entityMapper[$entityClassName] = new EntityMapper($entityClassName);
        }

        return $this->entityMapper[$entityClassName];
    }

    /**
     * TODO: add lazy relations and remove this fn
     * @param $relations
     * @return array
     */
    protected function filterRelations($relations)
    {
        foreach ($relations as $index => $relation) {
            if (!property_exists($relation, 'mode') ||
                $relation->mode !== 'eager' ||
                ($relation->type !== 'OneToMany' && $relation->type !== 'ManyToOne')
            ) {
                unset($relations[$index]);
            }
        }
        return $relations;
    }
}