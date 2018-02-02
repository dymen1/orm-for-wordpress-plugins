<?php
namespace Dorans\Competition\Repository;

use Dorans\Competition\Entity\Base\AbstractEntity;
use Dorans\Competition\Entity\Game;
use Dorans\Competition\Entity\Team;
use Dorans\Competition\Util\EntityMapper;
use Dorans\Competition\Util\QueryBuilder;

class GameRepository extends BaseEntityRepository
{
    public function getByCompetitionOrderByPlayedAt($competition)
    {
        $criteria = array(
            'competition_id' => $competition
        );

        $entityClassName = $this->getEntityClassName();
        $entityTableName = $this->getEntityTableName($entityClassName);

        $qb = new QueryBuilder($entityClassName);
        $qb->addSelect('e0.*')
            ->from($entityTableName, 'e0');
        $this->addRelationJoinsToQueryBuilder($qb);
        foreach ($criteria as $property => $value) {
            $qb->addWhere("e0." . $property . " = " . $value);
        }
        $qb->addOrderBy('played_at DESC');

        $query = $qb->getQuery();
        $result = $this->dbConnection->get_results($query);

        if ($qb->hasJoins()) {
            $result = $this->formatResult($result);
        }

        return $this->assocArrayToObjectArray($result, $entityClassName, true);
    }
}