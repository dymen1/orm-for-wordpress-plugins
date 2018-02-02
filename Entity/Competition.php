<?php
namespace Dorans\Competition\Entity;

use Dorans\Competition\Entity\Base\AbstractEntity;

class Competition extends AbstractEntity
{
    protected $tableName = 'competitions';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @relation({"type": "OneToMany", "class": "Dorans\Competition\Entity\Team"})
     * @var array
     */
    protected $teams = array();

    /**
     * @relation({"type": "OneToMany", "class": "Dorans\Competition\Entity\Game"})
     * @var array
     */
    protected $games = array();

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

    // region teams
    /**
     * @return array
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @param array $teams
     * @return $this
     * @throws \Exception
     */
    public function addTeams($teams)
    {
        if (!is_array($teams)) {
            throw new \Exception('param teams must be of type array.');
        }

        /** @var Team $team */
        foreach ($teams as $team) {
            $team->setCompetition($this);
            $this->teams[] = $team;
        }
        return $this;
    }

    /**
     * @param array $teams
     * @return $this
     * @throws \Exception
     */
    public function removeTeams($teams)
    {
        if (!is_array($teams)) {
            throw new \Exception('param teams must be of type array.');
        }

        /** @var Team $team */
        foreach ($teams as $team) {
            if ($key = array_search($team, $this->teams) !== false) {
                unset($this->teams[$key]);
            } else {
                throw new \Exception('object with id: ' . $team->getId() . ' was given to ' . __FUNCTION__ . ' but was not found in collection');
            }
        }
        return $this;
    }
    // endregion

    // region games
    /**
     * @return array
     */
    public function getGames()
    {
        return $this->games;
    }

    /**
     * @param array $games
     * @return $this
     * @throws \Exception
     */
    public function addGames($games)
    {
        if (!is_array($games)) {
            throw new \Exception('param games must be of type array.');
        }

        /** @var Game $game */
        foreach ($games as $game) {
            $game->setCompetition($this);
            $this->games[] = $game;
        }
        return $this;
    }

    /**
     * @param array $games
     * @return $this
     * @throws \Exception
     */
    public function removeGames($games)
    {
        if (!is_array($games)) {
            throw new \Exception('param games must be of type array.');
        }

        /** @var Game $game */
        foreach ($games as $game) {
            if ($key = array_search($game, $this->games) !== false) {
                unset($this->games[$key]);
            } else {
                throw new \Exception('object with id: ' . $game->getId() . ' was given to ' . __FUNCTION__ . ' but was not found in collection');
            }
        }
        return $this;
    }
    // endregion
}