<?php
namespace Dorans\Competition\Repository;

use Dorans\Competition\Entity\Base\AbstractEntity;
use Dorans\Competition\Entity\Game;
use Dorans\Competition\Entity\Team;
use Dorans\Competition\Util\EntityMapper;
use Dorans\Competition\Util\QueryBuilder;

class TeamRepository extends BaseEntityRepository
{
    public function getByCompetitionOrderByGamesWon($competition)
    {
        $criteria = array(
            'competition_id' => $competition
        );

        $entityClassName = $this->getEntityClassName();
        $entityTableName = $this->getEntityTableName($entityClassName);
        $gameTableName = $this->getEntityTableName(Game::class);
        $subQuery1 = '(SELECT count(*) FROM ' . $gameTableName . ' AS game ' .
            'WHERE game.competition_id = ' . $competition . ' ' .
            'AND game.winner_id = e0.id) AS wins';
        $subQuery2 = '(SELECT count(*) FROM ' . $gameTableName . ' AS game ' .
            'WHERE game.competition_id = ' . $competition . ' ' .
            'AND game.loser_id = e0.id) AS losses';

        $qb = new QueryBuilder($entityClassName);
        $qb->addSelect('e0.*')
            ->addSelect($subQuery1)
            ->addSelect($subQuery2)
            ->from($entityTableName, 'e0');
        $this->addRelationJoinsToQueryBuilder($qb);
        foreach ($criteria as $property => $value) {
            $qb->addWhere("e0." . $property . " = " . $value);
        }
        $qb->addOrderBy('wins DESC');
        $qb->addOrderBy('losses ASC');

        $query = $qb->getQuery();
        $result = $this->dbConnection->get_results($query);// TODO: find out why this is returning team x players rows instead of 1

        if ($qb->hasJoins()) {
            $result = $this->formatResult($result);
        }

        $returnArray = array();
        foreach ($result as $row) {
            $wins = $row->wins;
            $losses = $row->losses;
            unset($row->wins);
            unset($row->losses);

            $returnObject = (object)array(
                'wins' => $wins,
                'losses' => $losses,
                'team' => $this->assocArrayToObjectArray(array($row), $entityClassName),
            );
            $returnArray[] = $returnObject;
        }
        return $returnArray;
    }
}