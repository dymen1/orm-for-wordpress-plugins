<?php
namespace Dorans\Competition\Util;

class QueryBuilder
{
    /**
     * db adapter
     *
     * @var string
     */
    protected $dbAdapter;

    /**
     * TODO: nothing is done with this yet
     * FQN of the target entity
     *
     * @var string
     */
    protected $entityClassName;

    /** @var array */
    protected $select = array();
    /** @var string */
    protected $from = '';
    /** @var array */
    protected $join = array();
    /** @var array */
    protected $where = array();
    /** @var array */
    protected $orderBy = array();

    /**
     * BaseEntityRepository constructor.
     * @param null|string $entityClassName
     * @param $dbAdapter
     */
    public function __construct($entityClassName = null, $dbAdapter = 'MYSQL')
    {
        $this->entityClassName = $entityClassName;
        $this->dbAdapter = $dbAdapter;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        $query = '';
        $query .= $this->getSelect();
        $query .= $this->getFrom();
        $query .= $this->getJoin();
        $query .= $this->getWhere();
        $query .= $this->getOrderBy();
        return $query . ';';
    }

    /**
     * @param $part
     * @return $this
     */
    public function addSelect($part)
    {
        $this->select[] = $part;
        return $this;
    }

    /**
     * @return string
     */
    protected function getSelect()
    {
        $parts = $this->select;
        $sql = 'SELECT ';
        $lastPart = end($parts);
        foreach ($parts as $part) {
            $sql .= $part;
            if ($part !== $lastPart) {
                $sql .= ', ';
            }
        }
        return $sql;
    }

    /**
     * @param $part
     * @param $alias
     * @return $this
     */
    public function from($part, $alias)
    {
        $this->from = $part . ' AS ' . $alias;
        return $this;
    }

    /**
     * @return string
     */
    protected function getFrom()
    {
        return ' FROM ' . $this->from;
    }

    public function hasJoins() {
        return !empty($this->join);
    }

    /**
     * $type 'left' || 'inner'
     *
     * @param $part
     * @param string $type
     * @return $this
     */
    public function addJoin($part, $type = 'left')
    {
        $this->join[] = array(
            'type' => $type,
            'part' => $part,
        );
        return $this;
    }

    /**
     * @return string
     */
    protected function getJoin()
    {
        $parts = $this->join;
        $sql = '';
        foreach ($parts as $join) {
            $sql .= ' ' . strtoupper($join['type']) . ' JOIN ' . $join['part'];
        }
        return $sql;
    }

    /**
     * @param $part
     * @return $this
     */
    public function addWhere($part)
    {
        $this->where[] = $part;
        return $this;
    }

    /**
     * @return string
     */
    protected function getWhere()
    {
        $parts = $this->where;
        if (empty($parts)) {
            return '';
        }
        $sql = ' WHERE ';
        $lastPart = end($parts);
        foreach ($parts as $part) {
            $sql .= $part;
            if ($part !== $lastPart) {
                $sql .= ' AND ';
            }
        }
        return $sql;
    }

    /**
     * @param $part
     * @return $this
     */
    public function addOrderBy($part)
    {
        $this->orderBy[] = $part;
        return $this;
    }

    /**
     * @return string
     */
    protected function getOrderBy()
    {
        $parts = $this->orderBy;
        if (empty($parts)) {
            return '';
        }
        $sql = ' ORDER BY ';
        $lastPart = end($parts);
        foreach ($parts as $part) {
            $sql .= $part;
            if ($part !== $lastPart) {
                $sql .= ' , ';
            }
        }
        return $sql;
    }

    /**
     * @param $entityClassName
     * @return null
     * @throws \Exception
     */
    protected function getEntityClassName($entityClassName)
    {
        if ($entityClassName === null) {
            $entityClassName = $this->entityClassName;
        }

        if (!class_exists($entityClassName)) {
            throw new \Exception('"' . $entityClassName . '" does not exist.."');
        }

        return $entityClassName;
    }
}