<?php


namespace App\Util\Doctrine;


use Doctrine\DBAL\Query\QueryBuilder;

class DoctrineHelper
{
    public const UNION = 'UNION';
    public const UNION_ALL = 'UNION ALL';
    public CONST STATEMENT_TIMEOUT = '5min';

    /**
     * @param array $queryBuilders
     * @param string $uniotType
     * @return string
     */
    public static function unionQueryBuilders(array $queryBuilders, $uniotType = self::UNION)
    {
        $imploded = implode(
            ') ' . $uniotType . ' (',
            array_map(
                function (QueryBuilder $q) {
                    return $q->getSQL();
                },
                $queryBuilders
            )
        );
        return '(' . $imploded . ')';
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    public static function addSubSelectFromQueryBuilder(QueryBuilder $queryBuilder): string
    {
        return '(' . $queryBuilder->getSQL() . ')';
    }
}