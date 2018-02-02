<?php
namespace Dorans\Competition\Service;

use Dorans\Competition\Entity\Competition;
use Dorans\Competition\Entity\Game;
use Dorans\Competition\Repository\BaseEntityRepository;
use Dorans\Competition\Repository\GameRepository;
use Exception;

class GameEntityService extends BaseEntityService
{
    protected $teamEntityClass = Game::class;

    public function getByCompetition($competitie)
    {
        global $wpdb;
        $repository = new GameRepository($wpdb, $this->teamEntityClass);
        try {
            if ($competitie instanceof Competition) {
                $competitie = $competitie->getId();
            }
            return $repository->findBy(array('competition_id' => $competitie));
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
            return array();
        }
    }

    public function getByCompetitionOrderByPlayedAt($competition)
    {
        global $wpdb;
        $repository = new GameRepository($wpdb, $this->teamEntityClass);
        try {
            if ($competition instanceof Competition) {
                $competition = $competition->getId();
            }

            return $repository->getByCompetitionOrderByPlayedAt($competition);
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
            return array();
        }
    }
}