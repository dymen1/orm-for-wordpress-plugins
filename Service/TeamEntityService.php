<?php
namespace Dorans\Competition\Service;

use Dorans\Competition\Entity\Competition;
use Dorans\Competition\Entity\Team;
use Dorans\Competition\Repository\BaseEntityRepository;
use Dorans\Competition\Repository\TeamRepository;
use Exception;

class TeamEntityService extends BaseEntityService
{
    protected $teamEntityClass = Team::class;

    public function getByCompetition($competition)
    {
        global $wpdb;
        $repository = new TeamRepository($wpdb, $this->teamEntityClass);
        try {
            if ($competition instanceof Competition) {
                $competition = $competition->getId();
            }
            return $repository->findBy(array('competition_id' => $competition));
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
            return array();
        }
    }

    public function getByCompetitionOrderByGamesWon($competition)
    {
        global $wpdb;
        $repository = new TeamRepository($wpdb, $this->teamEntityClass);
        try {
            if ($competition instanceof Competition) {
                $competition = $competition->getId();
            }

            return $repository->getByCompetitionOrderByGamesWon($competition);
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
            return array();
        }
    }
}