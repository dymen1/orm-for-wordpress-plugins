<?php
namespace Dorans\Competition\Entity;

use Dorans\Competition\Entity\Base\AbstractEntity;

class Team extends AbstractEntity
{
    protected $tableName = 'teams';

    /**
     * @relation({"type": "ManyToOne", "class": "Dorans\Competition\Entity\Competition"})
     * @var Competition
     */
    protected $competition;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $coach = '';

    /**
     * @var string
     */
    protected $logo = '';

    /**
     * @var string
     */
    protected $teamPicture = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $phoneNr = '';

    /**
     * @relation({"type": "OneToMany", "class": "Dorans\Competition\Entity\Player", "mode": "eager"})
     * @var array
     */
    protected $players = array();

    // region competition
    /**
     * @return Competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return $this
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;
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

    // region coach
    /**
     * @return string
     */
    public function getCoach()
    {
        return $this->coach;
    }

    /**
     * @param string $coach
     */
    public function setCoach($coach)
    {
        $this->coach = $coach;
    }
    // endregion

    // region logo
    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }
    // endregion

    // region teamPicture
    /**
     * @return string
     */
    public function getTeamPicture()
    {
        return $this->teamPicture;
    }

    /**
     * @param string $teamPicture
     */
    public function setTeamPicture($teamPicture)
    {
        $this->teamPicture = $teamPicture;
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

    // region players
    /**
     * @return array
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param array $players
     * @return $this
     * @throws \Exception
     */
    public function addPlayers($players)
    {
        if (!is_array($players)) {
            throw new \Exception('param players must be of type array.');
        }

        /** @var Player $player */
        foreach ($players as $player) {
            $player->setTeam($this);
            $this->players[] = $player;
        }
        return $this;
    }

    /**
     * @param array $players
     * @return $this
     * @throws \Exception
     */
    public function removePlayers($players)
    {
        if (!is_array($players)) {
            throw new \Exception('param players must be of type array.');
        }

        /** @var Player $player */
        foreach ($players as $player) {
            if ($key = array_search($player, $this->players) !== false) {
                unset($this->players[$key]);
            } else {
                throw new \Exception('object with id: ' . $player->getId() . ' was given to ' . __FUNCTION__ . ' but was not found in collection');
            }
        }
        return $this;
    }
    // endregion
}