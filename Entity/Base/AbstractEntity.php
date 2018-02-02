<?php
namespace Dorans\Competition\Entity\Base;

abstract class AbstractEntity
{
    // region DbRelatedStuff
    private $pluginTableNamePrefix = 'dorans_competition';

    /**
     * Base name of the table this object is stored in
     * !attention! this will be prefixed by the constructor
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
    // endregion

    /**
     * Identifier
     *
     * @var integer
     */
    protected $id = 0;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct()
    {
        if (empty($this->tableName)) {
            throw new \Exception('No table name set');
        }
        $this->tableName = $this->pluginTableNamePrefix . '_' . $this->tableName;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     * @throws \Exception
     */
    public function setId($id)
    {
        if ($this->id !== 0) {
            throw new \Exception('Cannot change the id of an Entity!!!');
        }

        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * This function should only be used by the mapper!
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * This function should only be used by the mapper!
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}