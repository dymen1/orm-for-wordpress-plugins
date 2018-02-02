<?php
namespace Dorans\Competition\Entity;

use Dorans\Competition\Entity\Base\AbstractEntity;

class Player extends AbstractEntity
{
    protected $tableName = 'players';

    protected $apiId = null;

    protected $rankTier = null;

    protected $rankDivision = null;

    /**
     * @relation({"type": "ManyToOne", "class": "Dorans\Competition\Entity\Team"})
     * @var Team
     */
    protected $team;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $nickname = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $phoneNr = '';

    // region apiId
    /**
     * @return null
     */
    public function getApi()
    {
        return $this->apiId;
    }

    /**
     * @param null $apiId
     * @return Player
     */
    public function setApi($apiId)
    {
        $this->apiId = $apiId;
        return $this;
    }

    /**
     * @return null
     */
    public function getApiId()
    {
        return $this->apiId;
    }

    /**
     * @param null $apiId
     * @return Player
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
        return $this;
    }
    // endregion

    // region rankTier
    /**
     * @return null
     */
    public function getRankTier()
    {
        return $this->rankTier;
    }

    /**
     * @param null $rankTier
     * @return Player
     */
    public function setRankTier($rankTier)
    {
        $this->rankTier = $rankTier;
        return $this;
    }

    // region rankDivision
    /**
     * @return null
     */
    public function getRankDivision()
    {
        return $this->rankDivision;
    }

    /**
     * @param null $rankDivision
     * @return Player
     */
    public function setRankDivision($rankDivision)
    {
        $this->rankDivision = $rankDivision;
        return $this;
    }
    // endregion

    // region team
    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param Team $team
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;
        return $this;
    }
    // endregion

    // region name
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    // endregion

    // region nickname
    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     * @return $this
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }
    // endregion

    // region email
    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    // endregion

    // region phoneNr
    /**
     * @return string
     */
    public function getPhoneNr()
    {
        return $this->phoneNr;
    }

    /**
     * @param string $phoneNr
     */
    public function setPhoneNr($phoneNr)
    {
        $this->phoneNr = $phoneNr;
    }
    // endregion
}