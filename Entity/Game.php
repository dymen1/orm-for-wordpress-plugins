<?php
namespace Dorans\Competition\Entity;

use Dorans\Competition\Entity\Base\AbstractEntity;
use Dorans\Competition\Util\DateTime;

class Game extends AbstractEntity
{
    protected $tableName = 'games';

    /**
     * @relation({"type": "ManyToOne", "class": "Dorans\Competition\Entity\Competition"})
     * @var Competition
     */
    protected $competition;

    /**
     * @relation({"type": "ManyToOne", "class": "Dorans\Competition\Entity\Team", "mode": "eager"})
     * @var Team
     */
    protected $winner;

    /**
     * @relation({"type": "ManyToOne", "class": "Dorans\Competition\Entity\Team", "mode": "eager"})
     * @var Team
     */
    protected $loser;

    /**
     * @var string
     */
    protected $result = '';

    /**
     * @var \DateTime
     */
    protected $playedAt;

    /**
     * @var string
     */
    protected $extraIdentifier = '';

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

    // region winner
    /**
     * @return Team
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @param Team $winner
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;
    }
    // endregion

    // region loser
    /**
     * @return Team
     */
    public function getLoser()
    {
        return $this->loser;
    }

    /**
     * @param Team $loser
     */
    public function setLoser($loser)
    {
        $this->loser = $loser;
    }
    // endregion

    // region result
    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
    // endregion

    // region playedAt
    /**
     * @return \DateTime
     */
    public function getPlayedAt()
    {
        return $this->playedAt;
    }

    /**
     * TODO: move the logic here to a fn in: \Dorans\Competition\Util\DateTime
     *
     * @param \DateTime $playedAt
     * @param string $format
     * @throws \Exception
     */
    public function setPlayedAt($playedAt, $format = DateTime::MYSQL_W_SECONDS)
    {
        if (is_string($playedAt)) {
            $dateTimeObject = (new \DateTime())->createFromFormat($format, $playedAt);
            if ($dateTimeObject === false) {
                $format = DateTime::DATETIME_LOCAL;
                $dateTimeObject = (new DateTime())->createFromFormat($format, $playedAt);
                if ($dateTimeObject === false) {
                    throw new \Exception('Could not parse given string to valid DateTime.');
                }
            }
            $playedAt = $dateTimeObject;
        }
        $this->playedAt = $playedAt;
    }
    // endregion

    // region extraIdentifier
    /**
     * @return string
     */
    public function getExtraIdentifier()
    {
        return $this->extraIdentifier;
    }

    /**
     * @param string $extraIdentifier
     */
    public function setExtraIdentifier($extraIdentifier)
    {
        $this->extraIdentifier = $extraIdentifier;
    }
    // endregion
}