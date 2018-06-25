<?php

namespace KRG\EasyAdminExtensionBundle\Filter;

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class FilterQueryBuilder
{

    public static function bindQueryBuilder(QueryBuilder $queryBuilder, array $entityConfig, array $data) {
        $fields = $entityConfig['filter']['fields'];
        foreach ($fields as $idx => $field) {
            if (isset($data[sprintf('f%d', $idx)])) {
                $_data = $data[sprintf('f%d', $idx)];

                if ($_data instanceof Collection) {
                    $_data = $_data->toArray();
                }

                if (!empty($_data) && isset($field['query_builder_callback']) && is_callable($field['query_builder_callback'])) {
                    call_user_func($field['query_builder_callback'], $queryBuilder, $field, $_data);
                }
            }
        }
    }

    private static function addJoinQueryBuilder(QueryBuilder $queryBuilder, array $field) {

        $mapping = $field['metadata'][0];

        $previousAlias = $queryBuilder->getRootAliases()[0];
        if (count($field['metadata']) > 1) {
            foreach (array_reverse($field['metadata']) as $_mapping) {
                if (isset($_mapping['targetEntity'])) {
                    $queryBuilder->innerJoin(sprintf('%s.%s', $previousAlias, $_mapping['fieldName']), $_mapping['entityAlias']);
                    $previousAlias = $_mapping['entityAlias'];
                }
            }
        }

        if (!isset($mapping['entityAlias']) || $mapping['entityAlias'] === null) {
            $mapping['entityAlias'] = $previousAlias;
        }

        return $mapping;
    }

    public static function addQueryBuilder(QueryBuilder $queryBuilder, array $field, $data)
    {
        if (!is_scalar($data)) {
            return;
        }

        $mapping = self::addJoinQueryBuilder($queryBuilder, $field);

        $queryBuilder->andWhere(sprintf('%s.%s = :%s', $mapping['entityAlias'], $mapping['fieldName'], $field['name']));
        $queryBuilder->setParameter($field['name'], $data);
    }

    public static function addQueryBuilderText(QueryBuilder $queryBuilder, array $field, string $data)
    {
        $mapping = self::addJoinQueryBuilder($queryBuilder, $field);

        $words = str_word_count(strtolower($data), 1, '0123456789-_');
        $orx = new Orx();
        foreach ($words as $idx => $word) {
            $orx->add(sprintf('LOWER(%s.%s) LIKE :%s_%d', $mapping['entityAlias'], $mapping['fieldName'], $field['name'], $idx));
            $queryBuilder->setParameter(sprintf('%s_%d', $field['name'], $idx), sprintf('%%%s%%', $word));
        }
        $queryBuilder->andWhere($orx);
    }

    public static function addQueryBuilderChoice(QueryBuilder $queryBuilder, array $field, array $data)
    {
        $mapping = self::addJoinQueryBuilder($queryBuilder, $field);

        $fieldName = !isset($mapping['targetEntity']) ? $mapping['fieldName'] : 'id';

        $queryBuilder->andWhere(sprintf('%s.%s in (:%s)', $mapping['entityAlias'], $fieldName, $field['name']));
        $queryBuilder->setParameter($field['name'], $data);
    }

    public static function addQueryBuilderRange(QueryBuilder $queryBuilder, array $field, array $data)
    {
        $mapping = self::addJoinQueryBuilder($queryBuilder, $field);

        if ($data['min'] && $data['max']) {
            $queryBuilder->andWhere(sprintf('%s.%s BETWEEN :%s_min AND :%s_max', $mapping['entityAlias'], $mapping['fieldName'], $field['name'], $field['name']));
            $queryBuilder->setParameter(sprintf('%s_min', $field['name']), $data['min']);
            $queryBuilder->setParameter(sprintf('%s_max', $field['name']), $data['max']);
        } else {
            if ($data['min']) {
                $queryBuilder->andWhere(sprintf('%s.%s > :%s_min', $mapping['entityAlias'], $mapping['fieldName'], $field['name']));
                $queryBuilder->setParameter(sprintf('%s_min', $field['name']), $data['min']);
            } else {
                if ($data['max']) {
                    $queryBuilder->andWhere(sprintf('%s.%s < :%s_max', $mapping['entityAlias'], $mapping['fieldName'], $field['name']));
                    $queryBuilder->setParameter(sprintf('%s_max', $field['name']), $data['max']);
                }
            }
        }
    }
}